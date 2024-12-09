<?php

namespace App\Observers;

use Illuminate\Support\Facades\{DB, Log, Cache};
use App\Models\{TransaksiDo, Operasional, Perusahaan, LaporanKeuangan};
use Filament\Notifications\Notification;

class LaporanKeuanganObserver
{
    protected function createLaporan(array $data): void
    {
        try {
            // Pastikan data minimal yang diperlukan
            $requiredFields = [
                'tanggal',
                'jenis_transaksi',
                'kategori',
                'nominal',
                'sumber_transaksi',
                'referensi_id'
            ];

            foreach ($requiredFields as $field) {
                if (!isset($data[$field])) {
                    throw new \Exception("Field {$field} wajib diisi");
                }
            }

            // Set default values jika tidak ada
            $data['sub_kategori'] = $data['sub_kategori'] ?? '-';
            $data['nomor_referensi'] = $data['nomor_referensi'] ?? '-';
            $data['pihak_terkait'] = $data['pihak_terkait'] ?? '-';
            $data['cara_pembayaran'] = $data['cara_pembayaran'] ?? 'Tunai';
            $data['keterangan'] = $data['keterangan'] ?? '-';
            $data['mempengaruhi_kas'] = $data['mempengaruhi_kas'] ?? true;

            // Create laporan
            $laporan = LaporanKeuangan::create($data);

            // Log success dengan informasi minimal
            Log::info('Laporan dibuat: ' . $laporan->id);
        } catch (\Exception $e) {
            Log::error('Error: ' . $e->getMessage());
            throw $e;
        }
    }



    public function handleTransaksiDO(TransaksiDo $transaksiDo)
    {
        try {
            DB::beginTransaction();

            // Untuk data yang dihapus, hanya buat laporan tanpa mempengaruhi saldo
            if ($transaksiDo->trashed()) {
                if ($transaksiDo->cara_bayar === 'Tunai') {
                    $this->createLaporan([
                        'tanggal' => $transaksiDo->tanggal,
                        'jenis_transaksi' => 'Pengeluaran',
                        'kategori' => 'DO',
                        'sub_kategori' => 'Pembayaran DO',
                        'nominal' => $transaksiDo->sisa_bayar,
                        'sumber_transaksi' => 'DO',
                        'referensi_id' => $transaksiDo->id,
                        'nomor_referensi' => $transaksiDo->nomor,
                        'pihak_terkait' => $transaksiDo->penjual->nama,
                        'cara_pembayaran' => 'Tunai',
                        'keterangan' => "Pembatalan DO #{$transaksiDo->nomor}",
                        'mempengaruhi_kas' => false
                    ]);
                }

                DB::commit();
                return;
            }

            // Proses normal untuk non-deleted records
            // Menggunakan cache untuk data perusahaan
            $perusahaan = Cache::remember('perusahaan', 300, function () {
                return Perusahaan::lockForUpdate()->first();
            });
            
            if (!$perusahaan) {
                throw new \Exception('Data perusahaan tidak ditemukan');
            }

            // Proses normal untuk transaksi baru/update
            if ($transaksiDo->cara_bayar === 'Tunai') {
                $pemasukan = $transaksiDo->upah_bongkar + $transaksiDo->biaya_lain + $transaksiDo->pembayaran_hutang;
                if ($pemasukan > 0) {
                    $perusahaan->increment('saldo', $pemasukan);
                }

                if ($transaksiDo->sisa_bayar > 0) {
                    $perusahaan->decrement('saldo', $transaksiDo->sisa_bayar);
                }

                $this->handleTransaksiTunai($transaksiDo, $perusahaan);
            } else {
                $this->handleTransaksiNonTunai($transaksiDo, $perusahaan);
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    protected function handleTransaksiTunai(TransaksiDo $transaksiDo, Perusahaan $perusahaan)
    {
        // Catat komponen pemasukan
        $komponenPemasukan = [
            ['sub_kategori' => 'Upah Bongkar', 'nominal' => $transaksiDo->upah_bongkar],
            ['sub_kategori' => 'Biaya Lain', 'nominal' => $transaksiDo->biaya_lain],
            ['sub_kategori' => 'Bayar Hutang', 'nominal' => $transaksiDo->pembayaran_hutang],
        ];

        // Total pemasukan tunai
        $totalPemasukan = 0;

        foreach ($komponenPemasukan as $komponen) {
            if ($komponen['nominal'] > 0) {
                $this->createLaporan([
                    'tanggal' => $transaksiDo->tanggal,
                    'jenis_transaksi' => 'Pemasukan',
                    'kategori' => 'DO',
                    'sub_kategori' => $komponen['sub_kategori'],
                    'nominal' => $komponen['nominal'],
                    'sumber_transaksi' => 'DO',
                    'referensi_id' => $transaksiDo->id,
                    'nomor_referensi' => $transaksiDo->nomor,
                    'pihak_terkait' => $transaksiDo->penjual->nama,
                    'cara_pembayaran' => 'Tunai',
                    'keterangan' => "Pemasukan tunai DO #{$transaksiDo->nomor}",
                    'mempengaruhi_kas' => true,
                ]);

                $totalPemasukan += $komponen['nominal'];
            }
        }

        // Catat pengeluaran sisa bayar
        if ($transaksiDo->sisa_bayar > 0) {
            $this->createLaporan([
                'tanggal' => $transaksiDo->tanggal,
                'jenis_transaksi' => 'Pengeluaran',
                'kategori' => 'DO',
                'sub_kategori' => 'Pembayaran DO',
                'nominal' => $transaksiDo->sisa_bayar,
                'sumber_transaksi' => 'DO',
                'referensi_id' => $transaksiDo->id,
                'nomor_referensi' => $transaksiDo->nomor,
                'pihak_terkait' => $transaksiDo->penjual->nama,
                'cara_pembayaran' => 'Tunai',
                'keterangan' => "Pembayaran DO #{$transaksiDo->nomor}",
                'mempengaruhi_kas' => true,
            ]);
        }

        // Log perubahan saldo dengan informasi minimal
        Log::info("DO #{$transaksiDo->nomor} selesai: +{$totalPemasukan}, -{$transaksiDo->sisa_bayar}");
    }

    protected function handleTransaksiNonTunai(TransaksiDo $transaksiDo, Perusahaan $perusahaan)
    {
        // Catat semua komponen pemasukan tunai jika ada
        $komponenPemasukan = [
            ['sub_kategori' => 'Upah Bongkar', 'nominal' => $transaksiDo->upah_bongkar],
            ['sub_kategori' => 'Biaya Lain', 'nominal' => $transaksiDo->biaya_lain],
            ['sub_kategori' => 'Bayar Hutang', 'nominal' => $transaksiDo->pembayaran_hutang],
        ];

        $totalPemasukan = 0;
        $pihakTerkait = $transaksiDo->penjual ? $transaksiDo->penjual->nama : 'Penjual tidak ditemukan';

        // Catat setiap komponen pemasukan tunai
        foreach ($komponenPemasukan as $komponen) {
            if ($komponen['nominal'] > 0) {
                $this->createLaporan([
                    'tanggal' => $transaksiDo->tanggal,
                    'jenis_transaksi' => 'Pemasukan',
                    'kategori' => 'DO',
                    'sub_kategori' => $komponen['sub_kategori'],
                    'nominal' => $komponen['nominal'],
                    'sumber_transaksi' => 'DO',
                    'referensi_id' => $transaksiDo->id,
                    'nomor_referensi' => $transaksiDo->nomor,
                    'pihak_terkait' => $pihakTerkait,
                    'cara_pembayaran' => 'Tunai',
                    'keterangan' => "Pemasukan tunai DO #{$transaksiDo->nomor}",
                    'mempengaruhi_kas' => true,
                ]);

                $totalPemasukan += $komponen['nominal'];
            }
        }

        // Catat transaksi non tunai untuk sisa bayar
        if ($transaksiDo->sisa_bayar > 0) {
            $this->createLaporan([
                'tanggal' => $transaksiDo->tanggal,
                'jenis_transaksi' => 'Pengeluaran',
                'kategori' => 'DO',
                'sub_kategori' => 'Pembayaran DO',
                'nominal' => $transaksiDo->sisa_bayar,
                'sumber_transaksi' => 'DO',
                'referensi_id' => $transaksiDo->id,
                'nomor_referensi' => $transaksiDo->nomor,
                'pihak_terkait' => $pihakTerkait,
                'cara_pembayaran' => $transaksiDo->cara_bayar,
                'keterangan' => "Pembayaran DO via {$transaksiDo->cara_bayar}",
                'mempengaruhi_kas' => false,
            ]);
        }

        // Log perubahan saldo dengan informasi minimal
        Log::info("DO non-tunai #{$transaksiDo->nomor} selesai: +{$totalPemasukan}");
    }
}