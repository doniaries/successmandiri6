<?php

namespace App\Observers;

use App\Models\{TransaksiDo, Penjual, Perusahaan, LaporanKeuangan};
use Illuminate\Support\Facades\{DB, Log};
use Filament\Notifications\Notification;

class TransaksiDoObserver
{
    protected $laporanObserver;

    public function __construct(LaporanKeuanganObserver $laporanObserver)
    {
        $this->laporanObserver = $laporanObserver;
    }

    public function creating(TransaksiDo $transaksiDo)
    {
        try {
            DB::beginTransaction();

            // Validasi data wajib
            $this->validateRequiredFields($transaksiDo);

            // Hitung total DO
            $transaksiDo->sub_total = $transaksiDo->tonase * $transaksiDo->harga_satuan;

            // Komponen pengurangan
            $komponenPengurangan =
                $transaksiDo->upah_bongkar +
                $transaksiDo->biaya_lain +
                $transaksiDo->pembayaran_hutang;

            // Hitung sisa bayar
            $transaksiDo->sisa_bayar = $transaksiDo->sub_total - $komponenPengurangan;
            $transaksiDo->sisa_bayar = max(0, $transaksiDo->sisa_bayar);

            // Validasi saldo untuk pembayaran tunai
            if ($transaksiDo->cara_bayar === 'Tunai') {
                $this->validateCompanyBalance($transaksiDo);
            }

            // Update hutang penjual jika ada pembayaran
            if ($transaksiDo->pembayaran_hutang > 0) {
                $this->handleHutangPenjual($transaksiDo);
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creating TransaksiDo:', [
                'error' => $e->getMessage(),
                'data' => $transaksiDo->toArray()
            ]);
            throw $e;
        }
    }

    public function created(TransaksiDo $transaksiDo)
    {
        // Generate laporan keuangan
        $this->laporanObserver->handleTransaksiDO($transaksiDo);
    }

    public function updating(TransaksiDo $transaksiDo)
    {
        try {
            DB::beginTransaction();

            $oldTransaksi = TransaksiDo::find($transaksiDo->id);

            // Rollback hutang lama jika ada perubahan
            if ($oldTransaksi->pembayaran_hutang > 0) {
                $oldTransaksi->penjual->increment('hutang', $oldTransaksi->pembayaran_hutang);
            }

            // Proses hutang baru jika ada
            if ($transaksiDo->pembayaran_hutang > 0) {
                $this->handleHutangPenjual($transaksiDo);
            }

            // Validasi saldo untuk pembayaran tunai
            if ($transaksiDo->cara_bayar === 'Tunai') {
                $this->validateCompanyBalance($transaksiDo);
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function updated(TransaksiDo $transaksiDo)
    {
        // Hapus laporan lama
        LaporanKeuangan::where([
            'sumber_transaksi' => 'DO',
            'referensi_id' => $transaksiDo->id
        ])->delete();

        // Generate laporan baru
        $this->laporanObserver->handleTransaksiDO($transaksiDo);
    }

    public function deleted(TransaksiDo $transaksiDo)
    {
        try {
            DB::beginTransaction();

            // Kembalikan hutang penjual
            if ($transaksiDo->pembayaran_hutang > 0 && $transaksiDo->penjual) {
                $transaksiDo->penjual->increment('hutang', $transaksiDo->pembayaran_hutang);
            }

            // Hapus laporan keuangan
            LaporanKeuangan::where([
                'sumber_transaksi' => 'DO',
                'referensi_id' => $transaksiDo->id
            ])->delete();

            DB::commit();

            $this->sendDeleteNotification($transaksiDo);
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    // Helper Methods
    protected function validateRequiredFields(TransaksiDo $transaksiDo)
    {
        $required = ['penjual_id', 'tanggal', 'tonase', 'harga_satuan'];

        foreach ($required as $field) {
            if (!$transaksiDo->$field) {
                throw new \Exception("Field {$field} wajib diisi");
            }
        }
    }

    protected function handleHutangPenjual(TransaksiDo $transaksiDo)
    {
        $penjual = Penjual::lockForUpdate()->find($transaksiDo->penjual_id);

        if (!$penjual) {
            throw new \Exception('Data penjual tidak ditemukan');
        }

        if ($transaksiDo->pembayaran_hutang > $penjual->hutang) {
            throw new \Exception(
                "Pembayaran hutang Rp " . number_format($transaksiDo->pembayaran_hutang, 0, ',', '.') .
                    " melebihi sisa hutang Rp " . number_format($penjual->hutang, 0, ',', '.')
            );
        }

        $penjual->decrement('hutang', $transaksiDo->pembayaran_hutang);
        $transaksiDo->sisa_hutang_penjual = $penjual->fresh()->hutang;
    }

    protected function validateCompanyBalance(TransaksiDo $transaksiDo)
    {
        $perusahaan = Perusahaan::lockForUpdate()->first();
        if (!$perusahaan) {
            throw new \Exception('Data perusahaan tidak ditemukan');
        }

        // Untuk transaksi tunai, periksa saldo cukup untuk membayar sisa
        if ($transaksiDo->sisa_bayar > $perusahaan->saldo) {
            throw new \Exception(
                "Saldo tidak mencukupi untuk pembayaran tunai.\n" .
                "Saldo: Rp " . number_format($perusahaan->saldo, 0, ',', '.') . "\n" .
                "Dibutuhkan: Rp " . number_format($transaksiDo->sisa_bayar, 0, ',', '.')
            );
        }
    }

    protected function sendDeleteNotification(TransaksiDo $transaksiDo)
    {
        $message = "DO #{$transaksiDo->nomor} telah dibatalkan\n";

        if ($transaksiDo->cara_bayar === 'Tunai') {
            $totalPemasukan = $transaksiDo->upah_bongkar +
                $transaksiDo->biaya_lain +
                $transaksiDo->pembayaran_hutang;

            $message .= "\nPerubahan Saldo:";
            if ($totalPemasukan > 0) {
                $message .= "\n- Pembatalan pemasukan: -Rp " .
                    number_format($totalPemasukan, 0, ',', '.');
            }
            if ($transaksiDo->sisa_bayar > 0) {
                $message .= "\n- Pengembalian pengeluaran: +Rp " .
                    number_format($transaksiDo->sisa_bayar, 0, ',', '.');
            }
        }

        if ($transaksiDo->pembayaran_hutang > 0) {
            $message .= "\n\nInfo Hutang:";
            $message .= "\n- Hutang dikembalikan: +Rp " .
                number_format($transaksiDo->pembayaran_hutang, 0, ',', '.');

            if ($transaksiDo->penjual) {
                $message .= "\n- Hutang terkini: Rp " .
                    number_format($transaksiDo->penjual->hutang, 0, ',', '.');
            }
        }

        Notification::make()
            ->title('Transaksi DO Dibatalkan')
            ->warning()
            ->body($message)
            ->send();
    }

    protected function prepareForSave(TransaksiDo $transaksiDo)
    {
        // Generate nomor DO jika baru
        if (!$transaksiDo->nomor) {
            $transaksiDo->nomor = $transaksiDo->generateMonthlyNumber();
        }

        // Set default values
        $transaksiDo->upah_bongkar = $transaksiDo->upah_bongkar ?? 0;
        $transaksiDo->biaya_lain = $transaksiDo->biaya_lain ?? 0;
        $transaksiDo->pembayaran_hutang = $transaksiDo->pembayaran_hutang ?? 0;
        $transaksiDo->cara_bayar = $transaksiDo->cara_bayar ?? 'Tunai';

        // Kalkulasi
        $transaksiDo->sub_total = $transaksiDo->tonase * $transaksiDo->harga_satuan;

        $komponenPengurangan =
            $transaksiDo->upah_bongkar +
            $transaksiDo->biaya_lain +
            $transaksiDo->pembayaran_hutang;

        $transaksiDo->sisa_bayar = max(0, $transaksiDo->sub_total - $komponenPengurangan);
    }

    public function restored(TransaksiDo $transaksiDo)
    {
        try {
            DB::beginTransaction();

            // Restore related data
            $this->laporanObserver->handleTransaksiDO($transaksiDo);

            if ($transaksiDo->pembayaran_hutang > 0) {
                $this->handleHutangPenjual($transaksiDo);
            }

            DB::commit();

            Notification::make()
                ->title('Transaksi DO Dipulihkan')
                ->success()
                ->body("DO #{$transaksiDo->nomor} berhasil dipulihkan")
                ->send();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function forceDeleted(TransaksiDo $transaksiDo)
    {
        // Hapus permanen semua data terkait
        LaporanKeuangan::where([
            'sumber_transaksi' => 'DO',
            'referensi_id' => $transaksiDo->id
        ])->forceDelete();

        Notification::make()
            ->title('Transaksi DO Dihapus Permanen')
            ->warning()
            ->body("DO #{$transaksiDo->nomor} telah dihapus secara permanen")
            ->send();
    }


}
