<?php

namespace App\Observers;

use Illuminate\Support\Facades\{DB, Log};
use App\Models\{TransaksiDo, Operasional, Perusahaan, LaporanKeuangan};
use Filament\Notifications\Notification;

class LaporanKeuanganObserver
{
    public function handleTransaksiDO(TransaksiDo $transaksiDo)
    {
        try {
            DB::beginTransaction();

            // Ambil data perusahaan dengan locking
            $perusahaan = Perusahaan::lockForUpdate()->first();
            if (!$perusahaan) {
                throw new \Exception('Data perusahaan tidak ditemukan');
            }

            // Load relasi penjual untuk menghindari N+1
            $transaksiDo->load('penjual');

            // Simpan saldo awal untuk logging
            $saldoAwal = $perusahaan->saldo;

            // Handle berdasarkan cara bayar
            if ($transaksiDo->cara_bayar === 'Tunai') {
                // Untuk transaksi tunai, semua komponen mempengaruhi kas
                $pemasukan = $transaksiDo->upah_bongkar + $transaksiDo->biaya_lain + $transaksiDo->pembayaran_hutang;
                $pengeluaran = $transaksiDo->sisa_bayar;

                // Update saldo
                if ($pemasukan > 0) {
                    $perusahaan->increment('saldo', $pemasukan);
                }
                if ($pengeluaran > 0) {
                    $perusahaan->decrement('saldo', $pengeluaran);
                }

                // Buat laporan keuangan
                $this->handleTransaksiTunai($transaksiDo, $perusahaan);
            } else {
                // Untuk non-tunai, hanya komponen tunai yang mempengaruhi kas
                $pemasukanTunai = $transaksiDo->upah_bongkar + $transaksiDo->biaya_lain + $transaksiDo->pembayaran_hutang;
                if ($pemasukanTunai > 0) {
                    $perusahaan->increment('saldo', $pemasukanTunai);
                }

                // Buat laporan keuangan
                $this->handleTransaksiNonTunai($transaksiDo, $perusahaan);
            }

            DB::commit();

            // Log perubahan saldo
            Log::info('Transaksi DO selesai:', [
                'nomor' => $transaksiDo->nomor,
                'cara_bayar' => $transaksiDo->cara_bayar,
                'saldo_awal' => $saldoAwal,
                'saldo_akhir' => $perusahaan->fresh()->saldo,
                'selisih' => $perusahaan->fresh()->saldo - $saldoAwal
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error mencatat transaksi DO:', [
                'error' => $e->getMessage(),
                'transaksi' => $transaksiDo->toArray()
            ]);
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

        // Log perubahan saldo
        Log::info('Update saldo transaksi tunai:', [
            'nomor_do' => $transaksiDo->nomor,
            'total_pemasukan' => $totalPemasukan,
            'pengeluaran' => $transaksiDo->sisa_bayar,
            'saldo_akhir' => $perusahaan->fresh()->saldo
        ]);
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
                    'pihak_terkait' => $transaksiDo->penjual->nama,
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
                'pihak_terkait' => $transaksiDo->penjual->nama,
                'cara_pembayaran' => $transaksiDo->cara_bayar,
                'keterangan' => "Pembayaran DO via {$transaksiDo->cara_bayar}",
                'mempengaruhi_kas' => false,
            ]);
        }

        // Log perubahan saldo
        Log::info('Update saldo transaksi non-tunai:', [
            'nomor_do' => $transaksiDo->nomor,
            'total_pemasukan_tunai' => $totalPemasukan,
            'sisa_bayar_non_tunai' => $transaksiDo->sisa_bayar,
            'saldo_akhir' => $perusahaan->fresh()->saldo
        ]);
    }

    protected function createLaporan(array $data)
    {
        // Validasi data wajib
        $required = [
            'tanggal',
            'jenis_transaksi',
            'kategori',
            'nominal',
            'sumber_transaksi',
            'referensi_id',
            'pihak_terkait',
            'cara_pembayaran',
            'mempengaruhi_kas'
        ];

        foreach ($required as $field) {
            if (!isset($data[$field])) {
                throw new \Exception("Field {$field} wajib diisi");
            }
        }

        // Cek duplikasi
        $exists = LaporanKeuangan::where([
            'sumber_transaksi' => $data['sumber_transaksi'],
            'referensi_id' => $data['referensi_id'],
            'kategori' => $data['kategori'],
            'sub_kategori' => $data['sub_kategori'] ?? null,
            'nominal' => $data['nominal']
        ])->exists();

        if ($exists) {
            Log::info('Mencegah duplikasi transaksi:', $data);
            return null;
        }

        return LaporanKeuangan::create($data);
    }

    protected function updateSaldo(TransaksiDo $transaksiDo)
    {
        if ($transaksiDo->cara_bayar === 'Tunai') {
            $perusahaan = Perusahaan::lockForUpdate()->first();

            // Hitung komponen yang mempengaruhi saldo
            $pemasukan = $transaksiDo->pembayaran_hutang;
            $pengeluaran = $transaksiDo->sub_total;

            // Update saldo
            $perusahaan->decrement('saldo', $pengeluaran);
            if ($pemasukan > 0) {
                $perusahaan->increment('saldo', $pemasukan);
            }

            Log::info('Saldo updated:', [
                'pengeluaran' => $pengeluaran,
                'pemasukan' => $pemasukan,
                'saldo_akhir' => $perusahaan->fresh()->saldo
            ]);
        }
    }
}
