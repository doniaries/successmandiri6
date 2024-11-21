<?php

namespace App\Observers;

use Illuminate\Support\Facades\{DB, Log};
use App\Models\{TransaksiDo, Operasional, Perusahaan, LaporanKeuangan}; // Tambahkan use statement
use Filament\Notifications\Notification;

class LaporanKeuanganObserver
{

    public function handleTransaksiDO(TransaksiDo $transaksiDo)
    {
        try {
            DB::beginTransaction();

            // Ambil data perusahaan dengan locking untuk mencegah race condition
            $perusahaan = Perusahaan::lockForUpdate()->first();
            if (!$perusahaan) {
                throw new \Exception('Data perusahaan tidak ditemukan');
            }

            // Simpan saldo awal sebelum transaksi
            $saldoAwal = $perusahaan->saldo;

            // Eager load relasi penjual untuk mencegah n+1 query
            $transaksiDo->load('penjual');

            // 1. Pemasukan tunai (upah bongkar & biaya lain)
            $totalPemasukan = $this->catatPemasukanTunai($transaksiDo, $saldoAwal);

            // 2. Handle pembayaran hutang (jika ada)
            if ($transaksiDo->pembayaran_hutang > 0) {
                // Update hutang penjual
                $transaksiDo->penjual->decrement('hutang', $transaksiDo->pembayaran_hutang);

                $this->createLaporan([
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
                    'keterangan' => "Pembayaran hutang dari DO {$transaksiDo->nomor}",
                    'saldo_sebelum' => $saldoAwal + $totalPemasukan,
                    'saldo_sesudah' => $saldoAwal + $totalPemasukan + $transaksiDo->pembayaran_hutang
                ]);

                // Update saldo perusahaan (pemasukan tunai)
                $perusahaan->increment('saldo', $transaksiDo->pembayaran_hutang);
                $saldoAwal += $transaksiDo->pembayaran_hutang;
            }

            // 3. Handle sisa bayar berdasarkan cara bayar
            if ($transaksiDo->sisa_bayar > 0) {
                // Validasi saldo untuk pembayaran tunai
                if ($transaksiDo->cara_bayar === 'Tunai') {
                    if ($transaksiDo->sisa_bayar > $perusahaan->saldo) {
                        throw new \Exception(
                            "Saldo tidak mencukupi untuk pembayaran tunai.\n" .
                                "Saldo: Rp " . number_format($perusahaan->saldo, 0, ',', '.') . "\n" .
                                "Dibutuhkan: Rp " . number_format($transaksiDo->sisa_bayar, 0, ',', '.')
                        );
                    }

                    // Kurangi saldo perusahaan untuk pembayaran tunai
                    $perusahaan->decrement('saldo', $transaksiDo->sisa_bayar);
                }

                // Catat pengeluaran
                $saldoSebelum = $perusahaan->saldo;
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
                    'tipe_pihak' => 'penjual',
                    'cara_pembayaran' => $transaksiDo->cara_bayar,
                    'keterangan' => "Pembayaran DO {$transaksiDo->nomor} via {$transaksiDo->cara_bayar}",
                    'saldo_sebelum' => $saldoSebelum,
                    'saldo_sesudah' => $transaksiDo->cara_bayar === 'Tunai' ?
                        $saldoSebelum - $transaksiDo->sisa_bayar :
                        $saldoSebelum // Jika transfer tidak mempengaruhi saldo
                ]);
            }

            DB::commit();

            // Log final saldo
            Log::info('Transaksi DO selesai:', [
                'saldo_awal' => $saldoAwal,
                'saldo_akhir' => $perusahaan->fresh()->saldo,
                'cara_bayar' => $transaksiDo->cara_bayar,
                'nominal' => $transaksiDo->sisa_bayar
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error mencatat transaksi DO ke laporan:', [
                'error' => $e->getMessage(),
                'transaksi' => $transaksiDo->toArray()
            ]);
            throw $e;
        }
    }

    /**
     * Catat pemasukan tunai dari upah bongkar & biaya lain
     */
    private function catatPemasukanTunai(TransaksiDo $transaksiDo): void
    {
        $perusahaan = Perusahaan::first();
        $totalPemasukan = 0;

        // 1. Upah bongkar
        if ($transaksiDo->upah_bongkar > 0) {
            $this->createLaporan([
                'tanggal' => $transaksiDo->tanggal,
                'jenis_transaksi' => 'Pemasukan',
                'kategori' => 'DO',
                'sub_kategori' => 'Upah Bongkar',
                'nominal' => $transaksiDo->upah_bongkar,
                'sumber_transaksi' => 'DO',
                'referensi_id' => $transaksiDo->id,
                'nomor_referensi' => $transaksiDo->nomor,
                'pihak_terkait' => $transaksiDo->penjual->nama,
                'tipe_pihak' => 'penjual',
                'cara_pembayaran' => 'Tunai',
                'keterangan' => "Upah bongkar dari DO {$transaksiDo->nomor}"
            ]);
            $totalPemasukan += $transaksiDo->upah_bongkar;
        }

        // 2. Biaya lain
        if ($transaksiDo->biaya_lain > 0) {
            $this->createLaporan([
                'tanggal' => $transaksiDo->tanggal,
                'jenis_transaksi' => 'Pemasukan',
                'kategori' => 'DO',
                'sub_kategori' => 'Biaya Lain',
                'nominal' => $transaksiDo->biaya_lain,
                'sumber_transaksi' => 'DO',
                'referensi_id' => $transaksiDo->id,
                'nomor_referensi' => $transaksiDo->nomor,
                'pihak_terkait' => $transaksiDo->penjual->nama,
                'tipe_pihak' => 'penjual',
                'cara_pembayaran' => 'Tunai',
                'keterangan' => "Biaya lain dari DO {$transaksiDo->nomor}: {$transaksiDo->keterangan_biaya_lain}"
            ]);
            $totalPemasukan += $transaksiDo->biaya_lain;
        }

        // Update saldo perusahaan sekali saja
        if ($totalPemasukan > 0) {
            $perusahaan->increment('saldo', $totalPemasukan);
        }
    }

    /**
     * Create laporan with duplicate check
     */
    private function createLaporan(array $data): ?LaporanKeuangan
    {
        // Cek duplikasi dengan kriteria utama
        $exists = LaporanKeuangan::where([
            'kategori' => $data['kategori'],
            // 'jenis_transaksi' => $operasional->operasional === 'pemasukan' ? 'Pemasukan' : 'Pengeluaran',
            'jenis_transaksi' => $data['jenis_transaksi'],
            'sub_kategori' => $data['sub_kategori'],
            'referensi_id' => $data['referensi_id'],
            'nominal' => $data['nominal']
        ])->exists();

        if ($exists) {
            Log::info('Mencegah duplikasi transaksi:', array_merge($data, [
                'reason' => 'Transaksi dengan kriteria yang sama sudah ada'
            ]));
            return null;
        }

        return LaporanKeuangan::create($data);
    }

    /**
     * Log transaksi untuk tracking
     */
    private function logTransaksi(TransaksiDo $transaksiDo): void
    {
        Log::info('Transaksi DO berhasil dicatat:', [
            'nomor' => $transaksiDo->nomor,
            'penjual' => $transaksiDo->penjual->nama,
            'upah_bongkar' => $transaksiDo->upah_bongkar,
            'biaya_lain' => $transaksiDo->biaya_lain,
            'pembayaran_hutang' => $transaksiDo->pembayaran_hutang,
            'sisa_bayar' => $transaksiDo->sisa_bayar,
            'cara_bayar' => $transaksiDo->cara_bayar,
            'status' => 'success'
        ]);
    }

    /**
     * Handle operasional ke laporan keuangan
     * @param \App\Models\Operasional $operasional
     */

    public function handleOperasional(\App\Models\Operasional $operasional): void
    {
        try {
            DB::beginTransaction();

            // Lock perusahaan row untuk update
            $perusahaan = Perusahaan::lockForUpdate()->first();
            if (!$perusahaan) {
                throw new \Exception('Data perusahaan tidak ditemukan');
            }

            // Catat saldo sebelum transaksi
            $saldoSebelum = $perusahaan->saldo;

            // Update saldo berdasarkan jenis operasional
            if ($operasional->operasional === 'pemasukan') {
                $saldoSesudah = $saldoSebelum + $operasional->nominal;
                $perusahaan->increment('saldo', $operasional->nominal);
            } else {
                $saldoSesudah = $saldoSebelum - $operasional->nominal;
                $perusahaan->decrement('saldo', $operasional->nominal);
            }

            // Catat ke laporan keuangan dengan saldo
            $this->createLaporan([
                'tanggal' => $operasional->tanggal,
                'jenis_transaksi' => ucfirst($operasional->operasional),
                'kategori' => 'Operasional',
                'sub_kategori' => $operasional->kategori?->label() ?: '-',
                'nominal' => $operasional->nominal,
                'sumber_transaksi' => 'Operasional',
                'referensi_id' => $operasional->id,
                'nomor_referensi' => null,
                'pihak_terkait' => $operasional->nama,
                'tipe_pihak' => $operasional->tipe_nama,
                'cara_pembayaran' => 'Tunai',
                'keterangan' => $operasional->keterangan ?: '-',
                'saldo_sebelum' => $saldoSebelum,
                'saldo_sesudah' => $saldoSesudah
            ]);

            // Update saldo perusahaan
            if ($operasional->operasional === 'pemasukan') {
                $perusahaan->increment('saldo', $operasional->nominal);
            } else {
                $perusahaan->decrement('saldo', $operasional->nominal);
            }

            DB::commit();

            // Log perubahan saldo
            Log::info('Update saldo dari operasional:', [
                'saldo_sebelum' => $saldoSebelum,
                'nominal' => $operasional->nominal,
                'jenis' => $operasional->operasional,
                'saldo_sesudah' => $saldoSesudah
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
