<?php

namespace App\Observers;


use App\Models\{Operasional, Penjual, Perusahaan, LaporanKeuangan};
use App\Enums\KategoriOperasional;
use Illuminate\Support\Facades\{DB, Log};
use Filament\Notifications\Notification;
use Filament\Notifications\Actions\Action;

class OperasionalObserver
{
    protected $laporanObserver;

    public function __construct(LaporanKeuanganObserver $laporanObserver)
    {
        $this->laporanObserver = $laporanObserver;
    }

    public function created(Operasional $operasional): void
    {
        try {
            DB::beginTransaction();

            // Dapatkan data pihak terkait
            $pihakTerkait = match ($operasional->tipe_nama) {
                'penjual' => $operasional->penjual?->nama,
                'user' => $operasional->user?->name,
                'pekerja' => $operasional->pekerja?->nama,
                default => null
            };

            $subKategori = $operasional->kategori === 'bayar_hutang'
                ? "Pembayaran Hutang {$operasional->tipe_nama}"
                : ($operasional->kategori?->label() ?? '-');

            // SINGLE Create laporan keuangan
            LaporanKeuangan::create([
                'tanggal' => $operasional->tanggal,
                'jenis_transaksi' => ucfirst($operasional->operasional),
                'kategori' => 'Operasional',
                'sub_kategori' => $subKategori,
                'nominal' => $operasional->nominal,
                'sumber_transaksi' => 'Operasional',
                'referensi_id' => $operasional->id,
                'nomor_referensi' => sprintf('OP-%s', str_pad($operasional->id, 5, '0', STR_PAD_LEFT)),
                'pihak_terkait' => $pihakTerkait,
                'tipe_pihak' => $operasional->tipe_nama,
                'cara_pembayaran' => 'Tunai',
                'keterangan' => $operasional->keterangan
                    ? "{$operasional->keterangan}" . ($operasional->kategori === 'bayar_hutang' ? ' - Pembayaran Hutang' : '')
                    : ($operasional->kategori === 'bayar_hutang' ? 'Pembayaran Hutang' : '-'),
                'mempengaruhi_kas' => true,
                'saldo_sebelum' => optional(Perusahaan::first())->saldo ?? 0,
            ]);

            // 2. Proses perubahan hutang jika ada
            if ($operasional->kategori === 'bayar_hutang' || $operasional->kategori === 'pinjaman') {
                $this->processHutang($operasional);
            }

            // 3. Update saldo perusahaan
            $this->updateSaldoPerusahaan($operasional);

            DB::commit();

            // 4. Notifikasi sukses
            $this->showTransactionNotification($operasional, 'created');
        } catch (\Exception $e) {
            DB::rollBack();
            $this->logAndNotifyError('created', $e, $operasional);
            throw $e;
        }
    }

    public function updated(Operasional $operasional): void
    {
        try {
            DB::beginTransaction();

            // 1. Hapus laporan keuangan yang lama
            LaporanKeuangan::where([
                'kategori' => 'Operasional',
                'referensi_id' => $operasional->id
            ])->delete();

            // 2. Buat laporan keuangan baru
            $this->createLaporanKeuangan($operasional);

            // 3. Proses perubahan hutang jika ada
            if ($operasional->isDirty(['nominal', 'kategori'])) {
                $this->rollbackHutang($operasional->getOriginal());
                $this->processHutang($operasional);
            }

            // 4. Update saldo perusahaan
            $this->updateSaldoPerusahaan($operasional);

            DB::commit();

            $this->showTransactionNotification($operasional, 'updated');
        } catch (\Exception $e) {
            DB::rollBack();
            $this->logAndNotifyError('updated', $e, $operasional);
            throw $e;
        }
    }

    public function deleted(Operasional $operasional): void
    {
        try {
            DB::beginTransaction();

            // 1. Hapus laporan keuangan terkait
            LaporanKeuangan::where([
                'kategori' => 'Operasional',
                'referensi_id' => $operasional->id
            ])->delete();

            // 2. Rollback hutang jika ada
            $this->rollbackHutang($operasional);

            // 3. Kembalikan saldo perusahaan
            $this->rollbackSaldoPerusahaan($operasional);

            DB::commit();

            $this->showTransactionNotification($operasional, 'deleted');
        } catch (\Exception $e) {
            DB::rollBack();
            $this->logAndNotifyError('deleted', $e, $operasional);
            throw $e;
        }
    }

    /**
     * Handle the Operasional "restored" event.
     * Dipanggil setelah data di-restore dari soft delete
     */
    public function restored(Operasional $operasional): void
    {
        try {
            DB::beginTransaction();

            // 1. Catat lagi ke laporan keuangan
            $this->laporanObserver->handleOperasional($operasional);

            // 2. Proses ulang perubahan hutang jika ada
            $this->processHutang($operasional);

            DB::commit();

            // 3. Tampilkan notifikasi restore berhasil
            Notification::make()
                ->title('Data Berhasil Dipulihkan')
                ->icon('heroicon-o-check-circle')
                ->iconColor('success')
                ->body(
                    "Data operasional berikut telah berhasil dipulihkan:\n" .
                        "• Jenis: {$operasional->operasional}\n" .
                        "• Kategori: {$operasional->kategoriLabel}\n" .
                        "• Nominal: Rp " . number_format($operasional->nominal, 0, ',', '.')
                )
                ->actions([
                    Action::make('view')
                        ->label('Lihat Data')
                        ->url(route('filament.admin.resources.operasionals.edit', $operasional))
                        ->button()
                ])
                ->success()
                ->duration(5000)
                ->send();
        } catch (\Exception $e) {
            DB::rollBack();
            $this->logAndNotifyError('restored', $e, $operasional);
            throw $e;
        }
    }

    /**
     * Handle the Operasional "force deleted" event.
     * Dipanggil saat data dihapus permanen
     */
    public function forceDeleted(Operasional $operasional): void
    {
        try {
            DB::beginTransaction();

            // 1. Hapus data terkait di laporan keuangan
            LaporanKeuangan::where([
                'sumber_transaksi' => 'Operasional',
                'referensi_id' => $operasional->id
            ])->delete();

            // 2. Rollback hutang jika ada
            $this->rollbackHutang($operasional);

            DB::commit();

            // 3. Tampilkan notifikasi
            Notification::make()
                ->title('Data Terhapus Permanen')
                ->icon('heroicon-o-trash')
                ->iconColor('danger')
                ->body(
                    "Data operasional berikut telah dihapus permanen:\n" .
                        "• Jenis: {$operasional->operasional}\n" .
                        "• Kategori: {$operasional->kategoriLabel}\n" .
                        "• Nominal: Rp " . number_format($operasional->nominal, 0, ',', '.')
                )
                ->warning()
                ->duration(5000)
                ->send();
        } catch (\Exception $e) {
            DB::rollBack();
            $this->logAndNotifyError('force deleted', $e, $operasional);
            throw $e;
        }
    }

    // Helper Methods
    // Modifikasi method processHutang
    private function processHutang(Operasional $operasional): void
    {
        if ($operasional->kategori === 'bayar_hutang') {
            $pihak = match ($operasional->tipe_nama) {
                'penjual' => $operasional->penjual,
                'pekerja' => $operasional->pekerja,
                default => null
            };

            if (!$pihak) {
                throw new \Exception('Data pihak terkait tidak ditemukan');
            }

            // Validasi pembayaran tidak melebihi hutang
            if ($operasional->nominal > $pihak->hutang) {
                throw new \Exception('Pembayaran melebihi total hutang');
            }

            // Kurangi hutang
            $pihak->decrement('hutang', $operasional->nominal);

            Log::info('Hutang dibayar:', [
                'tipe' => $operasional->tipe_nama,
                'pihak' => $pihak->nama,
                'nominal' => $operasional->nominal,
                'sisa_hutang' => $pihak->fresh()->hutang
            ]);
        }
    }

    private function rollbackHutang($operasional): void
    {
        if (!in_array($operasional->kategori, ['bayar_hutang', 'pinjaman'])) {
            return;
        }

        $pihak = match ($operasional->tipe_nama) {
            'penjual' => $operasional->penjual,
            'pekerja' => $operasional->pekerja,
            default => null
        };

        if (!$pihak) return;

        if ($operasional->kategori === 'pinjaman') {
            $pihak->decrement('hutang', $operasional->nominal);
        } else {
            $pihak->increment('hutang', $operasional->nominal);
        }

        Log::info('Hutang dikembalikan:', [
            'operasional_id' => $operasional->id,
            'pihak' => $pihak->nama,
            'nominal' => $operasional->nominal
        ]);
    }

    private function updateSaldoPerusahaan(Operasional $operasional): void
    {
        $perusahaan = Perusahaan::first();
        if (!$perusahaan) {
            throw new \Exception('Data perusahaan tidak ditemukan');
        }

        if ($operasional->operasional === 'pemasukan') {
            $perusahaan->increment('saldo', $operasional->nominal);
        } else {
            // Validasi saldo cukup untuk pengeluaran
            if ($operasional->nominal > $perusahaan->saldo) {
                throw new \Exception(
                    "Saldo tidak mencukupi untuk pengeluaran.\n" .
                        "Saldo: Rp " . number_format($perusahaan->saldo, 0, ',', '.') . "\n" .
                        "Dibutuhkan: Rp " . number_format($operasional->nominal, 0, ',', '.')
                );
            }
            $perusahaan->decrement('saldo', $operasional->nominal);
        }

        Log::info('Saldo perusahaan diupdate:', [
            'jenis' => $operasional->operasional,
            'nominal' => $operasional->nominal,
            'saldo_akhir' => $perusahaan->fresh()->saldo
        ]);
    }


    private function rollbackSaldoPerusahaan(Operasional $operasional): void
    {
        $perusahaan = Perusahaan::first();
        if (!$perusahaan) return;

        // Reverse the effect on saldo
        if ($operasional->operasional === 'pemasukan') {
            $perusahaan->decrement('saldo', $operasional->nominal);
        } else {
            $perusahaan->increment('saldo', $operasional->nominal);
        }

        Log::info('Saldo dikembalikan:', [
            'operasional_id' => $operasional->id,
            'nominal' => $operasional->nominal,
            'saldo_akhir' => $perusahaan->fresh()->saldo
        ]);
    }



    private function showTransactionNotification(Operasional $operasional, string $action): void
    {
        $nominal = number_format($operasional->nominal, 0, ',', '.');

        Notification::make()
            ->title($this->getNotificationTitle($operasional, $action))
            ->success()
            ->icon('heroicon-o-check-circle')
            ->body(
                "Detail Transaksi:\n" .
                    "• Tanggal: {$operasional->tanggal->format('d/m/Y H:i')}\n" .
                    "• Jenis: " . ucfirst($operasional->operasional) . "\n" .
                    "• Kategori: " . ($operasional->kategori ? $operasional->kategori->label() : '-') . "\n" .  // Tambahkan null check
                    "• Nominal: Rp {$nominal}" .
                    ($operasional->keterangan ? "\n• Keterangan: {$operasional->keterangan}" : "")
            )
            ->duration(5000)
            ->send();
    }


    private function createLaporanKeuangan(Operasional $operasional): void
    {
        // Dapatkan data pihak terkait
        $pihakTerkait = match ($operasional->tipe_nama) {
            'penjual' => $operasional->penjual?->nama,
            'user' => $operasional->user?->name,
            'pekerja' => $operasional->pekerja?->nama,
            default => null
        };

        // Buat laporan keuangan
        LaporanKeuangan::create([
            'tanggal' => $operasional->tanggal,
            'jenis_transaksi' => ucfirst($operasional->operasional), // Pemasukan/Pengeluaran
            'kategori' => 'Operasional',
            'sub_kategori' => $operasional->kategori?->label() ?? '-',
            'nominal' => $operasional->nominal,
            'sumber_transaksi' => 'Operasional',
            'referensi_id' => $operasional->id,
            'nomor_referensi' => sprintf('OP-%s', str_pad($operasional->id, 5, '0', STR_PAD_LEFT)),
            'pihak_terkait' => $pihakTerkait,
            'tipe_pihak' => $operasional->tipe_nama,
            'cara_pembayaran' => 'Tunai',
            'keterangan' => $operasional->keterangan ?: '-',
            'mempengaruhi_kas' => true,
        ]);

        Log::info('Laporan Keuangan Operasional dibuat:', [
            'operasional_id' => $operasional->id,
            'jenis' => $operasional->operasional,
            'nominal' => $operasional->nominal,
            'kategori' => $operasional->kategori?->label()
        ]);
    }

    private function getNotificationTitle(Operasional $operasional, string $action): string
    {
        $actionText = match ($action) {
            'created' => 'Berhasil Dibuat',
            'updated' => 'Berhasil Diupdate',
            'deleted' => 'Berhasil Dihapus',
            'restored' => 'Berhasil Dipulihkan',
            default => 'Berhasil Diproses'
        };

        return "Transaksi Operasional {$actionText}";
    }

    //----------------///

    private function showNotification(string $title, string $body, string $message = '', string $type = 'success'): void
    {
        // Filament 3 style notification
        Notification::make()
            ->title($title)
            ->icon($this->getNotificationIcon($type))  // Tambahkan icon
            ->iconColor($type)  // Gunakan warna sesuai type
            ->body($message ?: $body)  // Body wajib di Filament 3
            ->persistent()
            ->duration(5000)    // Durasi tampil 5 detik
            ->send();
    }

    private function logAndNotifyError(string $action, \Exception $e, Operasional $operasional): void
    {
        Log::error("Error {$action} Operasional:", [
            'error' => $e->getMessage(),
            'operasional' => $operasional->toArray()
        ]);

        // Sederhanakan notifikasi error, hapus bagian modalContent
        Notification::make()
            ->title('Error!')
            ->icon('heroicon-o-x-circle')
            ->iconColor('danger')
            ->body("Terjadi kesalahan saat {$action} transaksi: " . $e->getMessage()) // Tambahkan pesan error langsung di body
            ->danger()
            ->persistent()
            ->duration(10000) // Durasi lebih lama untuk error (10 detik)
            ->send();
    }

    // Tambahkan method helper untuk icon
    private function getNotificationIcon(string $type): string
    {
        return match ($type) {
            'success' => 'heroicon-o-check-circle',
            'danger' => 'heroicon-o-x-circle',
            'warning' => 'heroicon-o-exclamation-triangle',
            default => 'heroicon-o-information-circle'
        };
    }
}
