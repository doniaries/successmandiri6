<?php

namespace App\Services;

use App\Models\{TransaksiDo, Operasional, Perusahaan, LaporanKeuangan};
use Carbon\Carbon;
use Illuminate\Support\Facades\{DB, Log};

class LaporanKeuanganService
{
    public function generatePdfReport($startDate, $endDate)
    {
        try {
            $perusahaan = Perusahaan::firstOrFail();

            $transaksiDo = TransaksiDo::with(['penjual', 'supir'])
                ->whereBetween('tanggal', [
                    Carbon::parse($startDate)->startOfDay(),
                    Carbon::parse($endDate)->endOfDay()
                ])
                ->orderBy('tanggal', 'asc')
                ->get();

            $operasional = Operasional::whereBetween('tanggal', [
                Carbon::parse($startDate)->startOfDay(),
                Carbon::parse($endDate)->endOfDay()
            ])->get();

            // Hitung jumlah transaksi per cara bayar
            $transaksiCount = [
                'tunai' => $transaksiDo->where('cara_bayar', 'Tunai')->count(),
                'transfer' => $transaksiDo->where('cara_bayar', 'Transfer')->count(),
                'cairDiluar' => $transaksiDo->where('cara_bayar', 'cair di luar')->count(),
                'belumDibayar' => $transaksiDo->where('cara_bayar', 'belum dibayar')->count() // Perbaikan lowercase
            ];

            // Hitung total pembayaran per jenis
            $pembayaran = [
                'tunai' => $transaksiDo->where('cara_bayar', 'Tunai')->sum('sisa_bayar'),
                'transfer' => $transaksiDo->where('cara_bayar', 'Transfer')->sum('sisa_bayar'),
                'cairDiluar' => $transaksiDo->where('cara_bayar', 'cair di luar')->sum('sisa_bayar'),
                'belumDibayar' => $transaksiDo->where('cara_bayar', 'belum dibayar')->sum('sisa_bayar') // Perbaikan lowercase
            ];


            $pemasukanOperasional = $operasional->where('operasional', 'pemasukan')->sum('nominal');
            $pengeluaranOperasional = $operasional->where('operasional', 'pengeluaran')->sum('nominal');
            $totalBayarHutang = $transaksiDo->sum('pembayaran_hutang');

            // TOTAL SALDO/UANG MASUK
            $totalPemasukan = ($pembayaran['tunai'] + $pembayaran['transfer']) + // Pemasukan tunai + transfer
                $pembayaran['cairDiluar'] + // Cair diluar
                $totalBayarHutang + // Bayar hutang
                $pemasukanOperasional; // Pemasukan operasional

            // PENGELUARAN/UANG KELUAR
            $totalPengeluaran = $transaksiDo->sum('sub_total') + // Total DO
                $pengeluaranOperasional; // Total operasional

            // SISA SALDO
            $sisaSaldo = $totalPemasukan - $totalPengeluaran;

            // Hitung total lainnya
            $totalTonase = $transaksiDo->sum('tonase');
            $totalSubTotal = $transaksiDo->sum('sub_total');
            $totalBiaya = $transaksiDo->sum(fn($item) => $item->biaya_lain + $item->upah_bongkar);

            Log::info('Report Calculations:', [
                'pemasukan_tunai_transfer' => $pembayaran['tunai'] + $pembayaran['transfer'],
                'pemasukan_cair_diluar' => $pembayaran['cairDiluar'],
                'bayar_hutang' => $totalBayarHutang,
                'pemasukan_operasional' => $pemasukanOperasional,
                'total_saldo_masuk' => $totalPemasukan,
                'total_pengeluaran' => $totalPengeluaran,
                'sisa_saldo' => $sisaSaldo,
                'transaksi_count' => $transaksiCount
            ]);

            return [
                'perusahaan' => $perusahaan,
                'transaksiDo' => $transaksiDo,
                'operasional' => $operasional,
                'totalPemasukan' => $totalPemasukan,
                'totalPengeluaran' => $totalPengeluaran,
                'saldoAwal' => $sisaSaldo,
                'pembayaran' => $pembayaran,
                'transaksiCount' => $transaksiCount,
                'totalTonase' => $totalTonase,
                'totalSubTotal' => $totalSubTotal,
                'totalBiaya' => $totalBiaya,
                'totalBayarHutang' => $totalBayarHutang,
                'pemasukanOperasional' => $pemasukanOperasional,
                'pengeluaranOperasional' => $pengeluaranOperasional,
                'tanggal' => $startDate
            ];
        } catch (\Exception $e) {
            Log::error('Error generating report:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }
}
