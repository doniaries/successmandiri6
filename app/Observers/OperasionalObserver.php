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

            // Tangani berdasarkan jenis operasional
            if ($operasional->kategori === 'bayar_hutang') {
                /// 1. Untuk bayar hutang, proses hutang dulu
                if ($operasional->kategori === 'bayar_hutang') {
                    $this->processHutang($operasional);
                }

                // 2. Lalu create laporan keuangan khusus bayar hutang
                $this->createLaporanKeuangan($operasional);
                LaporanKeuangan::create([
                    'tanggal' => $operasional->tanggal,
                    'jenis_transaksi' => ucfirst($operasional->operasional),
                    'kategori' => 'Operasional',
                    'sub_kategori' => "Pembayaran Hutang {$operasional->tipe_nama}",
                    'nominal' => $operasional->nominal,
                    'sumber_transaksi' => 'Operasional',
                    'referensi_id' => $operasional->id,
                    'nomor_referensi' => sprintf('OP-%s', str_pad($operasional->id, 5, '0', STR_PAD_LEFT)),
                    'pihak_terkait' => $operasional->nama,
                    'tipe_pihak' => $operasional->tipe_nama,
                    'cara_pembayaran' => 'Tunai',
                    'keterangan' => "Pembayaran hutang {$operasional->tipe_nama}" .
                        ($operasional->keterangan ? ": {$operasional->keterangan}" : ''),
                    'mempengaruhi_kas' => true,
                    'saldo_sebelum' => optional(Perusahaan::first())->saldo ?? 0,
                ]);
            } else {
                // Untuk operasional umum (non bayar hutang)
                // 1. Create laporan keuangan biasa
                LaporanKeuangan::create([
                    'tanggal' => $operasional->tanggal,
                    'jenis_transaksi' => ucfirst($operasional->operasional),
                    'kategori' => 'Operasional',
                    'sub_kategori' => $operasional->kategori?->label() ?? '-',
                    'nominal' => $operasional->nominal,
                    'sumber_transaksi' => 'Operasional',
                    'referensi_id' => $operasional->id,
                    'nomor_referensi' => sprintf('OP-%s', str_pad($operasional->id, 5, '0', STR_PAD_LEFT)),
                    'pihak_terkait' => $operasional->nama,
                    'tipe_pihak' => $operasional->tipe_nama,
                    'cara_pembayaran' => 'Tunai',
                    'keterangan' => $operasional->keterangan ?: '-',
                    'mempengaruhi_kas' => true,
                    'saldo_sebelum' => optional(Perusahaan::first())->saldo ?? 0,
                ]);
            }

            // 3. Update saldo perusahaan (berlaku untuk semua jenis)
            $this->updateSaldoPerusahaan($operasional);

            DB::commit();

            // Log untuk tracking
            Log::info('Operasional berhasil dibuat:', [
                'jenis' => $operasional->operasional,
                'kategori' => $operasional->kategori,
                'nominal' => $operasional->nominal,
                'tipe' => $operasional->tipe_nama,
                'pihak' => $operasional->nama
            ]);

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
            DB::beginTransaction();
            try {
                // 1. Dapatkan pihak terkait (dengan lock)
                $pihak = match ($operasional->tipe_nama) {
                    'penjual' => Penjual::lockForUpdate()->find($operasional->penjual_id),
                    'pekerja' => Pekerja::lockForUpdate()->find($operasional->pekerja_id),
                    default => null
                };

                if (!$pihak) {
                    throw new \Exception("Data {$operasional->tipe_nama} tidak ditemukan");
                }

                // 2. Ambil & validasi hutang sebelum update
                $hutangSebelum = $pihak->hutang;

                Log::info('Hutang sebelum proses:', [
                    'pihak' => $pihak->nama,
                    'hutang_awal' => $hutangSebelum,
                    'pembayaran' => $operasional->nominal
                ]);

                if ($operasional->nominal > $hutangSebelum) {
                    throw new \Exception(
                        "Pembayaran Rp " . number_format($operasional->nominal, 0, ',', '.') .
                            " melebihi total hutang Rp " . number_format($hutangSebelum, 0, ',', '.')
                    );
                }

                // 3. Hitung & update hutang baru
                $hutangBaru = $hutangSebelum - $operasional->nominal;

                // Update menggunakan query builder untuk memastikan perubahan
                DB::table($pihak->getTable())
                    ->where('id', $pihak->id)
                    ->update(['hutang' => $hutangBaru]);

                // 4. Refresh model dan verifikasi perubahan
                $pihak->refresh();

                if ($pihak->hutang !== $hutangBaru) {
                    throw new \Exception('Gagal mengupdate hutang - nilai tidak sesuai');
                }

                // 5. Catat riwayat jika fitur tersedia
                if (method_exists($pihak, 'riwayatPembayaran')) {
                    $pihak->riwayatPembayaran()->create([
                        'tanggal' => $operasional->tanggal,
                        'nominal' => $operasional->nominal,
                        'tipe' => $operasional->tipe_nama,
                        'operasional_id' => $operasional->id,
                        'keterangan' => "Pembayaran hutang via operasional"
                    ]);
                }

                // 6. Log hasil akhir
                Log::info('Hutang berhasil diupdate:', [
                    'pihak' => $pihak->nama,
                    'tipe' => $operasional->tipe_nama,
                    'hutang_awal' => $hutangSebelum,
                    'pembayaran' => $operasional->nominal,
                    'hutang_akhir' => $pihak->hutang,
                    'operasional_id' => $operasional->id
                ]);

                DB::commit();
            } catch (\Exception $e) {
                DB::rollBack();
                Log::error('Error saat proses hutang:', [
                    'error' => $e->getMessage(),
                    'operasional_id' => $operasional->id,
                    'pihak' => $operasional->nama ?? null
                ]);
                throw $e;
            }
        }
    }

    // Modifikasi method rollbackHutang juga agar konsisten
    private function rollbackHutang($operasional): void
    {
        if ($operasional->kategori === 'bayar_hutang') {
            $pihak = match ($operasional->tipe_nama) {
                'penjual' => $operasional->penjual,
                'pekerja' => $operasional->pekerja,
                default => null
            };

            if (!$pihak) return;

            $hutangSebelum = $pihak->hutang;

            // Kembalikan hutang yang sudah dibayar
            DB::transaction(function () use ($pihak, $operasional, $hutangSebelum) {
                $pihak->update([
                    'hutang' => $hutangSebelum + $operasional->nominal
                ]);

                // Refresh model
                $pihak->refresh();

                Log::info('Hutang dikembalikan:', [
                    'operasional_id' => $operasional->id,
                    'pihak' => $pihak->nama,
                    'hutang_sebelum' => $hutangSebelum,
                    'penambahan' => $operasional->nominal,
                    'hutang_sesudah' => $pihak->hutang
                ]);
            });

            // Hapus riwayat pembayaran jika ada
            if (method_exists($pihak, 'riwayatPembayaran')) {
                $pihak->riwayatPembayaran()
                    ->where('operasional_id', $operasional->id)
                    ->delete();
            }
        }
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
