<?php

namespace App\Observers;

use Illuminate\Support\Str;
use App\Services\CacheService;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\{DB, Log};
use App\Observers\LaporanKeuanganObserver;
use App\Models\{TransaksiDo, LaporanKeuangan, Perusahaan, Penjual};

class TransaksiDoObserver
{
    public function creating(TransaksiDo $transaksiDo)
    {
        try {
            // 1. Validasi data wajib
            $this->validateRequiredFields($transaksiDo);

            // 2. Simpan dan validasi hutang
            $this->handleHutangValidation($transaksiDo);

            // 3. Hitung total dan sisa bayar
            $transaksiDo->total = $transaksiDo->tonase * $transaksiDo->harga_satuan;

            // Hanya hitung pemasukan tunai jika cara bayar Tunai
            $totalPemasukan = $transaksiDo->cara_bayar === 'Tunai'
                ? ($transaksiDo->upah_bongkar + $transaksiDo->biaya_lain + $transaksiDo->pembayaran_hutang)
                : 0;

            $transaksiDo->sisa_bayar = max(0, $transaksiDo->total - $totalPemasukan);

            // 4. Validasi saldo perusahaan hanya untuk pembayaran tunai
            if ($transaksiDo->cara_bayar === 'Tunai') {
                $this->validateCompanyBalance($transaksiDo);
            }

            Log::info('Data DO Siap Disimpan:', [
                'nomor' => $transaksiDo->nomor,
                'cara_bayar' => $transaksiDo->cara_bayar,
                'total' => $transaksiDo->total,
                'pemasukan_tunai' => $totalPemasukan,
                'sisa_bayar' => $transaksiDo->sisa_bayar,
                'mempengaruhi_saldo' => $transaksiDo->cara_bayar === 'Tunai'
            ]);
        } catch (\Exception $e) {
            Log::error('Error Validasi TransaksiDO:', [
                'error' => $e->getMessage(),
                'data' => $transaksiDo->toArray()
            ]);
            throw $e;
        }
    }

    protected function validateRequiredFields(TransaksiDo $transaksiDo)
    {
        if (!$transaksiDo->tanggal) {
            throw new \Exception("Tanggal wajib diisi");
        }
        if (!$transaksiDo->penjual_id) {
            throw new \Exception("Penjual wajib dipilih");
        }
        if ($transaksiDo->tonase <= 0) {
            throw new \Exception("Tonase harus lebih dari 0");
        }
        if ($transaksiDo->harga_satuan <= 0) {
            throw new \Exception("Harga satuan harus lebih dari 0");
        }
    }
    protected function handleHutangValidation(TransaksiDo $transaksiDo)
    {
        if ($transaksiDo->penjual_id) {
            $penjual = Penjual::find($transaksiDo->penjual_id);
            if (!$penjual) {
                throw new \Exception("Penjual tidak ditemukan");
            }

            $transaksiDo->hutang_awal = $penjual->hutang ?? 0;

            if ($transaksiDo->pembayaran_hutang > $transaksiDo->hutang_awal) {
                throw new \Exception(
                    "Pembayaran hutang (Rp " . number_format($transaksiDo->pembayaran_hutang, 0, ',', '.') .
                        ") melebihi hutang penjual (Rp " . number_format($transaksiDo->hutang_awal, 0, ',', '.') . ")"
                );
            }

            $transaksiDo->sisa_hutang_penjual = max(0, $transaksiDo->hutang_awal - $transaksiDo->pembayaran_hutang);
        }
    }

    protected function validateCompanyBalance(TransaksiDo $transaksiDo)
    {
        $perusahaan = Perusahaan::first();
        if (!$perusahaan) {
            throw new \Exception("Data perusahaan tidak ditemukan");
        }

        if ($transaksiDo->sisa_bayar > $perusahaan->saldo) {
            throw new \Exception(
                "Saldo perusahaan tidak mencukupi untuk transaksi tunai.\n" .
                    "Saldo saat ini: Rp " . number_format($perusahaan->saldo, 0, ',', '.') . "\n" .
                    "Dibutuhkan: Rp " . number_format($transaksiDo->sisa_bayar, 0, ',', '.')
            );
        }
    }


    public function created(TransaksiDo $transaksiDo)
    {
        // Buat laporan keuangan sesuai cara bayar
        if ($transaksiDo->cara_bayar === 'Tunai') {
            $this->createCashTransactionReports($transaksiDo);
        } else {
            $this->createNonCashTransactionReports($transaksiDo);
        }
    }

    protected function createCashTransactionReports(TransaksiDo $transaksiDo)
    {
        // Definisi komponen transaksi tunai yang mempengaruhi saldo
        $components = [
            [
                'jenis' => 'Pemasukan',
                'kategori' => 'Upah Bongkar',
                'nominal' => $transaksiDo->upah_bongkar
            ],
            [
                'jenis' => 'Pemasukan',
                'kategori' => 'Biaya Lain',
                'nominal' => $transaksiDo->biaya_lain
            ],
            [
                'jenis' => 'Pemasukan',
                'kategori' => 'Bayar Hutang',
                'nominal' => $transaksiDo->pembayaran_hutang
            ],
            [
                'jenis' => 'Pengeluaran',
                'kategori' => 'Pembayaran DO',
                'nominal' => $transaksiDo->sisa_bayar
            ]
        ];

        foreach ($components as $component) {
            if ($component['nominal'] > 0) {
                $this->createLaporanKeuangan([
                    'tanggal' => $transaksiDo->tanggal,
                    'jenis_transaksi' => $component['jenis'],
                    'kategori' => 'DO', // Konsisten untuk kategori
                    'sub_kategori' => $component['kategori'],
                    'nominal' => $component['nominal'],
                    'sumber_transaksi' => 'DO',
                    'referensi_id' => $transaksiDo->id,
                    'nomor_referensi' => $transaksiDo->nomor,
                    'pihak_terkait' => $transaksiDo->penjual->nama,
                    'tipe_pihak' => 'Penjual',
                    'cara_pembayaran' => 'Tunai',
                    'mempengaruhi_kas' => true,
                    'keterangan' => "Transaksi DO #{$transaksiDo->nomor}"
                ]);
            }
        }
    }

    // TransaksiDoObserver.php

    // Perbaikan method createNonCashTransactionReports
    protected function createNonCashTransactionReports(TransaksiDo $transaksiDo)
    {
        try {
            DB::beginTransaction();

            // Get perusahaan with lock
            $perusahaan = Perusahaan::lockForUpdate()->first();
            if (!$perusahaan) {
                throw new \Exception("Data perusahaan tidak ditemukan");
            }

            // 1. Catat upah bongkar dan biaya lain sebagai pemasukan tunai
            $pemasukanTunai = $transaksiDo->upah_bongkar + $transaksiDo->biaya_lain;
            if ($pemasukanTunai > 0) {
                $this->createLaporanKeuangan([
                    'tanggal' => $transaksiDo->tanggal,
                    'jenis_transaksi' => 'Pemasukan',
                    'kategori' => 'DO',
                    'sub_kategori' => 'Pemasukan Tunai',
                    'nominal' => $pemasukanTunai,
                    'sumber_transaksi' => 'DO',
                    'referensi_id' => $transaksiDo->id,
                    'nomor_referensi' => $transaksiDo->nomor,
                    'pihak_terkait' => $transaksiDo->penjual->nama,
                    'tipe_pihak' => 'penjual',
                    'cara_pembayaran' => 'Tunai',
                    'mempengaruhi_kas' => true,
                    'keterangan' => "Pemasukan tunai (upah & biaya) DO #{$transaksiDo->nomor}"
                ]);

                // Update saldo perusahaan
                $perusahaan->increment('saldo', $pemasukanTunai);
            }

            // 2. Catat pembayaran hutang sebagai pemasukan tunai
            if ($transaksiDo->pembayaran_hutang > 0) {
                $this->createLaporanKeuangan([
                    'tanggal' => $transaksiDo->tanggal,
                    'jenis_transaksi' => 'Pemasukan',
                    'kategori' => 'DO',
                    'sub_kategori' => 'Bayar Hutang',
                    'nominal' => $transaksiDo->pembayaran_hutang,
                    'sumber_transaksi' => 'DO',
                    'referensi_id' => $transaksiDo->id,
                    'nomor_referensi' => $transaksiDo->nomor,
                    'pihak_terkait' => $transaksiDo->penjual->nama,
                    'tipe_pihak' => 'penjual',
                    'cara_pembayaran' => 'Tunai',
                    'mempengaruhi_kas' => true,
                    'keterangan' => "Pembayaran hutang DO #{$transaksiDo->nomor}"
                ]);

                // Update saldo perusahaan
                $perusahaan->increment('saldo', $transaksiDo->pembayaran_hutang);
            }

            // 3. Catat sisa bayar sebagai pemasukan non-tunai
            if ($transaksiDo->sisa_bayar > 0) {
                // Catat pemasukan sisa bayar
                $this->createLaporanKeuangan([
                    'tanggal' => $transaksiDo->tanggal,
                    'jenis_transaksi' => 'Pemasukan',
                    'kategori' => 'DO',
                    'sub_kategori' => 'Sisa Bayar DO',
                    'nominal' => $transaksiDo->sisa_bayar,
                    'sumber_transaksi' => 'DO',
                    'referensi_id' => $transaksiDo->id,
                    'nomor_referensi' => $transaksiDo->nomor,
                    'pihak_terkait' => $transaksiDo->penjual->nama,
                    'tipe_pihak' => 'penjual',
                    'cara_pembayaran' => $transaksiDo->cara_bayar,
                    'mempengaruhi_kas' => true,
                    'keterangan' => "Pemasukan {$transaksiDo->cara_bayar} DO #{$transaksiDo->nomor}"
                ]);

                // Update saldo perusahaan (pemasukan)
                $perusahaan->increment('saldo', $transaksiDo->sisa_bayar);

                // Catat pengeluaran sisa bayar
                $this->createLaporanKeuangan([
                    'tanggal' => $transaksiDo->tanggal,
                    'jenis_transaksi' => 'Pengeluaran',
                    'kategori' => 'DO',
                    'sub_kategori' => 'Pembayaran DO',
                    'nominal' => $transaksiDo->sisa_bayar,
                    'sumber_transaksi' => 'DO',
                    'referensi_id' => $transaksiDo->id,
                    'nomor_referensi' => $transaksiDo->nomor,
                    'pihak_terkait' => $transaksiDo->penjual->nama,
                    'tipe_pihak' => 'penjual',
                    'cara_pembayaran' => $transaksiDo->cara_bayar,
                    'mempengaruhi_kas' => true,
                    'keterangan' => "Pembayaran DO via {$transaksiDo->cara_bayar}"
                ]);

                // Update saldo perusahaan (pengeluaran)
                $perusahaan->decrement('saldo', $transaksiDo->sisa_bayar);
            }

            DB::commit();

            Log::info('Transaksi non-tunai berhasil dicatat:', [
                'nomor_do' => $transaksiDo->nomor,
                'pemasukan_tunai' => $pemasukanTunai,
                'pembayaran_hutang' => $transaksiDo->pembayaran_hutang,
                'sisa_bayar' => $transaksiDo->sisa_bayar,
                'cara_bayar' => $transaksiDo->cara_bayar,
                'saldo_akhir' => $perusahaan->fresh()->saldo
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error mencatat transaksi non-tunai:', [
                'error' => $e->getMessage(),
                'transaksi' => $transaksiDo->toArray()
            ]);
            throw $e;
        }
    }


    public function updating(TransaksiDo $transaksiDo)
    {
        // Validasi pembayaran hutang
        if ($transaksiDo->isDirty('pembayaran_hutang')) {
            if ($transaksiDo->pembayaran_hutang > $transaksiDo->hutang_awal) {
                throw new \Exception("Pembayaran hutang tidak boleh melebihi hutang awal");
            }
        }

        // Validasi perubahan cara bayar
        if ($transaksiDo->isDirty('cara_bayar')) {
            // Jika berubah ke Tunai, cek saldo
            if ($transaksiDo->cara_bayar === 'Tunai') {
                $perusahaan = Perusahaan::first();
                if ($transaksiDo->sisa_bayar > $perusahaan->saldo) {
                    throw new \Exception("Saldo tidak cukup untuk pembayaran tunai");
                }
            }
        }
    }

    public function updated(TransaksiDo $transaksiDo)
    {
        // Hapus laporan keuangan lama berdasarkan referensi_id
        LaporanKeuangan::where([
            'sumber_transaksi' => 'DO',
            'referensi_id' => $transaksiDo->id
        ])->delete();

        // Buat laporan keuangan baru
        app(LaporanKeuanganObserver::class)->handleTransaksiDO($transaksiDo);

        CacheService::clearTransaksiCache($transaksiDo->penjual_id);

        Log::info('TransaksiDO Updated:', [
            'nomor' => $transaksiDo->nomor,
            'changes' => $transaksiDo->getChanges(),
            'cara_bayar' => $transaksiDo->cara_bayar
        ]);
    }

    public function deleted(TransaksiDo $transaksiDo)
    {
        try {
            DB::beginTransaction();

            $perusahaan = Perusahaan::lockForUpdate()->first();
            if (!$perusahaan) {
                throw new \Exception('Data perusahaan tidak ditemukan');
            }

            // 1. Kembalikan saldo untuk pembayaran tunai
            if ($transaksiDo->cara_bayar === 'Tunai') {
                // Tambahkan saldo untuk pembatalan pengeluaran (sisa bayar)
                if ($transaksiDo->sisa_bayar > 0) {
                    $perusahaan->increment('saldo', $transaksiDo->sisa_bayar);
                }

                // Kurangi saldo untuk pembatalan pemasukan
                $totalPemasukan = $transaksiDo->upah_bongkar +
                    $transaksiDo->biaya_lain +
                    $transaksiDo->pembayaran_hutang;

                if ($totalPemasukan > 0) {
                    $perusahaan->decrement('saldo', $totalPemasukan);
                }

                // Log perubahan saldo
                Log::info('Saldo dikembalikan setelah pembatalan DO:', [
                    'no_do' => $transaksiDo->nomor,
                    'pembatalan_pemasukan' => $totalPemasukan,
                    'pembatalan_pengeluaran' => $transaksiDo->sisa_bayar,
                    'saldo_akhir' => $perusahaan->fresh()->saldo
                ]);
            }

            // 2. Kembalikan hutang penjual jika ada pembayaran hutang
            if ($transaksiDo->pembayaran_hutang > 0 && $transaksiDo->penjual) {
                $hutangSebelum = $transaksiDo->penjual->hutang;
                $transaksiDo->penjual->increment('hutang', $transaksiDo->pembayaran_hutang);

                Log::info('Hutang penjual dikembalikan:', [
                    'penjual' => $transaksiDo->penjual->nama,
                    'hutang_sebelum' => $hutangSebelum,
                    'penambahan' => $transaksiDo->pembayaran_hutang,
                    'hutang_sesudah' => $transaksiDo->penjual->fresh()->hutang
                ]);
            }

            // 3. Hapus laporan keuangan terkait
            LaporanKeuangan::where([
                'sumber_transaksi' => 'DO',
                'referensi_id' => $transaksiDo->id
            ])->delete();

            // 4. Bersihkan cache
            CacheService::clearTransaksiCache($transaksiDo->penjual_id);

            DB::commit();

            // 5. Kirim notifikasi
            $this->sendDeleteNotification($transaksiDo, $totalPemasukan ?? 0);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error Pembatalan TransaksiDO:', [
                'error' => $e->getMessage(),
                'transaksi' => $transaksiDo->toArray()
            ]);
            throw $e;
        }
    }

    public function restored(TransaksiDo $transaksiDo)
    {
        CacheService::clearTransaksiCache($transaksiDo->penjual_id);
    }

    public function forceDeleted(TransaksiDo $transaksiDo)
    {
        CacheService::clearTransaksiCache($transaksiDo->penjual_id);
    }

    private function createLaporanKeuangan(array $data): ?LaporanKeuangan
    {
        try {
            // 1. Validasi data wajib dengan pengecekan yang lebih ketat
            $requiredFields = [
                'tanggal',
                'jenis_transaksi',
                'kategori',
                'sub_kategori',
                'nominal',
                'sumber_transaksi',
                'referensi_id',
                'nomor_referensi',
                'pihak_terkait',
                'tipe_pihak',
                'cara_pembayaran',
                'keterangan',
                'mempengaruhi_kas' // Tambahkan field wajib
            ];

            foreach ($requiredFields as $field) {
                if (!isset($data[$field])) {
                    throw new \Exception("Field {$field} wajib diisi pada laporan keuangan");
                }
            }

            // 2. Cek duplikasi dengan kriteria yang lebih spesifik
            $exists = $this->checkDuplikasiTransaksi([
                'referensi_id' => $data['referensi_id'],
                'sumber_transaksi' => $data['sumber_transaksi'],
                'kategori' => $data['kategori'],
                'sub_kategori' => $data['sub_kategori'],
                'nominal' => $data['nominal']
            ]);

            if ($exists) {
                Log::info('Mencegah duplikasi transaksi:', [
                    'referensi_id' => $data['referensi_id'],
                    'kategori' => $data['kategori'],
                    'sub_kategori' => $data['sub_kategori'],
                    'nominal' => $data['nominal']
                ]);
                return null;
            }

            // 3. Buat laporan dengan data lengkap
            $laporan = LaporanKeuangan::create($data);

            // 4. Log berhasil dengan informasi lengkap
            Log::info('Laporan Keuangan berhasil dibuat:', [
                'id' => $laporan->id,
                'referensi' => $data['nomor_referensi'],
                'jenis' => $data['jenis_transaksi'],
                'kategori' => $data['kategori'],
                'nominal' => $data['nominal'],
                'mempengaruhi_kas' => $data['mempengaruhi_kas'],
                'cara_pembayaran' => $data['cara_pembayaran']
            ]);

            return $laporan;
        } catch (\Exception $e) {
            Log::error('Error membuat Laporan Keuangan:', [
                'error' => $e->getMessage(),
                'data' => $data,
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    /**
     * Check duplikasi transaksi dengan kriteria yang lebih spesifik
     */
    private function checkDuplikasiTransaksi(array $criteria): bool
    {
        return LaporanKeuangan::where([
            'referensi_id' => $criteria['referensi_id'],
            'sumber_transaksi' => $criteria['sumber_transaksi'],
            'kategori' => $criteria['kategori'],
            'sub_kategori' => $criteria['sub_kategori'],
            'nominal' => $criteria['nominal']
        ])->exists();
    }


    private function sendSuccessNotification(TransaksiDo $transaksiDo): void
    {
        $totalPemasukan = $transaksiDo->upah_bongkar + $transaksiDo->biaya_lain + $transaksiDo->pembayaran_hutang;
        $totalPengeluaran = $transaksiDo->sisa_bayar;
        $selisih = $totalPemasukan - $totalPengeluaran;

        $message = $this->buildNotificationMessage($transaksiDo, $totalPemasukan, $totalPengeluaran, $selisih);

        Notification::make()
            ->title('Transaksi DO Berhasil')
            ->body($message)
            ->success()
            ->send();
    }

    private function sendDeleteNotification(TransaksiDo $transaksiDo, float $totalPemasukan): void
    {
        $message = "DO #{$transaksiDo->nomor} telah dibatalkan\n\n";

        if ($transaksiDo->cara_bayar === 'Tunai') {
            $message .= "Perubahan Saldo:\n";
            if ($totalPemasukan > 0) {
                $message .= "- Pembatalan pemasukan: -Rp " . number_format($totalPemasukan, 0, ',', '.') . "\n";
            }
            if ($transaksiDo->sisa_bayar > 0) {
                $message .= "- Pengembalian pengeluaran: +Rp " . number_format($transaksiDo->sisa_bayar, 0, ',', '.') . "\n";
            }
        }

        if ($transaksiDo->pembayaran_hutang > 0) {
            $message .= "\nInfo Hutang:\n";
            $message .= "- Hutang dikembalikan: +Rp " . number_format($transaksiDo->pembayaran_hutang, 0, ',', '.') . "\n";
            if ($transaksiDo->penjual) {
                $message .= "- Hutang terkini: Rp " . number_format($transaksiDo->penjual->hutang, 0, ',', '.') . "\n";
            }
        }

        Notification::make()
            ->title('Transaksi DO Dibatalkan')
            ->body($message)
            ->warning()
            ->duration(5000)
            ->send();
    }

    private function buildNotificationMessage(TransaksiDo $transaksiDo, $totalPemasukan, $totalPengeluaran, $selisih): string
    {
        $message = "DO #{$transaksiDo->nomor}\n\n";
        $message .= "Detail Transaksi:\n";
        $message .= "- Tonase: " . number_format($transaksiDo->tonase, 0, ',', '.') . " Kg\n";
        $message .= "- Total DO: Rp " . number_format($transaksiDo->total, 0, ',', '.') . "\n";
        $message .= "- Cara Bayar: {$transaksiDo->cara_bayar}\n\n";

        $message .= "Pemasukan:\n";
        if ($transaksiDo->upah_bongkar > 0) {
            $message .= "- Upah Bongkar: Rp " . number_format($transaksiDo->upah_bongkar, 0, ',', '.') . "\n";
        }
        if ($transaksiDo->biaya_lain > 0) {
            $message .= "- Biaya Lain: Rp " . number_format($transaksiDo->biaya_lain, 0, ',', '.') . "\n";
        }
        if ($transaksiDo->pembayaran_hutang > 0) {
            $message .= "- Bayar Hutang: Rp " . number_format($transaksiDo->pembayaran_hutang, 0, ',', '.') . "\n";
        }
        $message .= "Total Pemasukan: Rp " . number_format($totalPemasukan, 0, ',', '.') . "\n\n";

        $message .= "Pengeluaran:\n";
        $message .= "- Sisa Bayar DO: Rp " . number_format($totalPengeluaran, 0, ',', '.') . "\n\n";

        $message .= "Selisih: Rp " . number_format($selisih, 0, ',', '.') . "\n";

        if ($transaksiDo->pembayaran_hutang > 0) {
            $message .= "\nInfo Hutang:\n";
            $message .= "- Hutang Awal: Rp " . number_format($transaksiDo->hutang_awal, 0, ',', '.') . "\n";
            $message .= "- Pembayaran: Rp " . number_format($transaksiDo->pembayaran_hutang, 0, ',', '.') . "\n";
            $message .= "- Sisa Hutang: Rp " . number_format($transaksiDo->sisa_hutang_penjual, 0, ',', '.');
        }

        // Tambah info kas
        if ($transaksiDo->cara_bayar === 'Tunai') {
            $message .= "\n\nTransaksi mempengaruhi saldo kas.";
        } else {
            $message .= "\n\nTransaksi tidak mempengaruhi saldo kas.";
        }

        return $message;
    }
}
