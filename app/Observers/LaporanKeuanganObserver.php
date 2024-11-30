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

            // Ambil data perusahaan dengan locking
            $perusahaan = Perusahaan::lockForUpdate()->first();
            if (!$perusahaan) {
                throw new \Exception('Data perusahaan tidak ditemukan');
            }

            // Simpan saldo awal
            $saldoAwal = $perusahaan->saldo;

            // Eager load relasi penjual
            $transaksiDo->load('penjual');

            // Handle transaksi berdasarkan cara bayar
            if ($transaksiDo->cara_bayar === 'Tunai') {
                $this->handleTransaksiTunai($transaksiDo, $perusahaan);
            } else {
                $this->handleTransaksiNonTunai($transaksiDo);
            }

            // Update hutang penjual jika ada pembayaran hutang
            if ($transaksiDo->pembayaran_hutang > 0) {
                $transaksiDo->penjual->decrement('hutang', $transaksiDo->pembayaran_hutang);
            }

            DB::commit();

            // Log final saldo
            Log::info('Transaksi DO selesai:', [
                'nomor' => $transaksiDo->nomor,
                'cara_bayar' => $transaksiDo->cara_bayar,
                'mempengaruhi_saldo' => $transaksiDo->cara_bayar === 'Tunai',
                'saldo_akhir' => $perusahaan->fresh()->saldo
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
    protected function handleTransaksiTunai(TransaksiDo $transaksiDo, Perusahaan $perusahaan)
    {
        // Catat dan update saldo untuk komponen tunai
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
                'mempengaruhi_kas' => true,
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
                'mempengaruhi_kas' => true,
                'keterangan' => "Biaya lain dari DO {$transaksiDo->nomor}: {$transaksiDo->keterangan_biaya_lain}"
            ]);
            $totalPemasukan += $transaksiDo->biaya_lain;
        }

        // 3. Pembayaran hutang
        if ($transaksiDo->pembayaran_hutang > 0) {
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
                'mempengaruhi_kas' => true,
                'keterangan' => "Pembayaran hutang dari DO {$transaksiDo->nomor}"
            ]);
            $totalPemasukan += $transaksiDo->pembayaran_hutang;
        }

        // Update saldo perusahaan untuk pemasukan
        if ($totalPemasukan > 0) {
            $perusahaan->increment('saldo', $totalPemasukan);
        }

        // 4. Sisa bayar (pengeluaran)
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
                'tipe_pihak' => 'penjual',
                'cara_pembayaran' => 'Tunai',
                'mempengaruhi_kas' => true,
                'keterangan' => "Pembayaran DO {$transaksiDo->nomor}"
            ]);

            // Update saldo untuk pengeluaran
            $perusahaan->decrement('saldo', $transaksiDo->sisa_bayar);
        }
    }

    protected function handleTransaksiNonTunai(TransaksiDo $transaksiDo)
    {
        $perusahaan = Perusahaan::lockForUpdate()->first();

        // 1. Upah bongkar & biaya lain sebagai pemasukan tunai
        $pemasukanTunai = $transaksiDo->upah_bongkar + $transaksiDo->biaya_lain;
        if ($pemasukanTunai > 0) {
            $this->createLaporan([
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
                'keterangan' => "Pemasukan tunai DO #{$transaksiDo->nomor}"
            ]);

            $perusahaan->increment('saldo', $pemasukanTunai);
        }

        // 2. Pembayaran hutang sebagai pemasukan tunai
        if ($transaksiDo->pembayaran_hutang > 0) {
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
                'mempengaruhi_kas' => true,
                'keterangan' => "Pembayaran hutang dari DO {$transaksiDo->nomor}"
            ]);

            $perusahaan->increment('saldo', $transaksiDo->pembayaran_hutang);
        }

        // 3. Sisa bayar sebagai pemasukan via transfer/cair luar
        if ($transaksiDo->sisa_bayar > 0) {
            // Catat pemasukan
            $this->createLaporan([
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

            $perusahaan->increment('saldo', $transaksiDo->sisa_bayar);

            // Catat pengeluaran
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
                'mempengaruhi_kas' => true,
                'keterangan' => "Pembayaran DO via {$transaksiDo->cara_bayar}"
            ]);

            $perusahaan->decrement('saldo', $transaksiDo->sisa_bayar);
        }
    }


    /**
     * Create laporan with duplicate check
     */
    private function createLaporan(array $data): ?LaporanKeuangan
    {
        try {
            // 1. Validasi data wajib
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
                'keterangan'
            ];

            foreach ($requiredFields as $field) {
                if (!isset($data[$field])) {
                    throw new \Exception("Field {$field} wajib diisi pada laporan keuangan");
                }
            }

            // 2. Cek duplikasi dengan kriteria yang lebih spesifik
            $exists = LaporanKeuangan::where([
                'kategori' => $data['kategori'],
                'jenis_transaksi' => $data['jenis_transaksi'],
                'sub_kategori' => $data['sub_kategori'],
                'referensi_id' => $data['referensi_id'],
                'nominal' => $data['nominal'],
                // Perbaikan: Gunakan langsung value dari data
                'tipe_pihak' => $data['tipe_pihak'], // Hapus $tipeNama->value
            ])->exists();

            if ($exists) {
                Log::info('Mencegah duplikasi transaksi:', array_merge($data, [
                    'reason' => 'Transaksi dengan kriteria yang sama sudah ada'
                ]));
                return null;
            }

            // 3. Tambahkan created_at dan updated_at
            $data['created_at'] = now();
            $data['updated_at'] = now();

            // 4. Create laporan dengan error handling
            $laporan = LaporanKeuangan::create($data);

            Log::info('Laporan Keuangan berhasil dibuat:', [
                'id' => $laporan->id,
                'referensi' => $data['nomor_referensi'],
                'jenis' => $data['jenis_transaksi'],
                'kategori' => $data['kategori'],
                'nominal' => $data['nominal']
            ]);

            return $laporan;
        } catch (\Exception $e) {
            Log::error('Error membuat Laporan Keuangan:', [
                'error' => $e->getMessage(),
                'data' => $data
            ]);
            throw $e;
        }
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
