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
            // Get base data
            $perusahaan = Perusahaan::firstOrFail();

            // Get transactions in date range
            $transaksiDo = TransaksiDo::with(['penjual', 'supir'])
                ->whereBetween('tanggal', [
                    Carbon::parse($startDate)->startOfDay(),
                    Carbon::parse($endDate)->endOfDay()
                ])
                ->orderBy('tanggal', 'asc')
                ->get();

            // Get operational transactions
            $operasional = Operasional::whereBetween('tanggal', [
                Carbon::parse($startDate)->startOfDay(),
                Carbon::parse($endDate)->endOfDay()
            ])->get();

            // Calculate payment breakdowns
            $pembayaran = [
                'tunai' => $transaksiDo->where('cara_bayar', 'Tunai')->sum('sisa_bayar'),
                'transfer' => $transaksiDo->where('cara_bayar', 'Transfer')->sum('sisa_bayar'),
                'cairDiluar' => $transaksiDo->where('cara_bayar', 'cair di luar')->sum('sisa_bayar'),
                'belumDiBayar' => $transaksiDo->where('cara_bayar', 'belum Dibayar')->sum('sisa_bayar')
            ];

            // Total hutang payments
            $totalBayarHutang = $transaksiDo->sum('pembayaran_hutang');

            // Calculate operational totals
            $pemasukanOperasional = $operasional->where('operasional', 'pemasukan')->sum('nominal');
            $pengeluaranOperasional = $operasional->where('operasional', 'pengeluaran')->sum('nominal');

            // Calculate total income components (TOTAL SALDO/UANG MASUK)
            $totalPemasukan = $totalBayarHutang +
                $pembayaran['tunai'] +
                $pembayaran['transfer'] +
                $pembayaran['cairDiluar'] +
                $pembayaran['belumDiBayar'] +
                $pemasukanOperasional;

            // Calculate total expenses (PENGELUARAN/UANG KELUAR)
            $totalPengeluaran = $transaksiDo->sum('sub_total') + $pengeluaranOperasional;

            // Calculate remaining balance (SISA SALDO)
            $sisaSaldo = $totalPemasukan - $totalPengeluaran;

            // Calculate additional totals
            $totalTonase = $transaksiDo->sum('tonase');
            $totalSubTotal = $transaksiDo->sum('sub_total');
            $totalBiaya = $transaksiDo->sum(function ($item) {
                return $item->biaya_lain + $item->upah_bongkar;
            });

            Log::info('Report Calculations:', [
                'date_range' => "$startDate to $endDate",
                'sisa_saldo' => $sisaSaldo,
                'total_pemasukan' => $totalPemasukan,
                'total_pengeluaran' => $totalPengeluaran,
                'pembayaran_breakdown' => $pembayaran,
                'operasional' => [
                    'pemasukan' => $pemasukanOperasional,
                    'pengeluaran' => $pengeluaranOperasional
                ]
            ]);

            return [
                'perusahaan' => $perusahaan,
                'transaksiDo' => $transaksiDo,
                'operasional' => $operasional,
                'saldoAwal' => $sisaSaldo,
                'totalPemasukan' => $totalPemasukan,
                'totalPengeluaran' => $totalPengeluaran,
                'pembayaran' => $pembayaran,
                'totalTonase' => $totalTonase,
                'totalSubTotal' => $totalSubTotal,
                'totalBiaya' => $totalBiaya,
                'totalBayarHutang' => $totalBayarHutang,
                'tanggal' => $startDate,
                'pemasukanOperasional' => $operasional->where('operasional', 'pemasukan'),
                'pengeluaranOperasional' => $operasional->where('operasional', 'pengeluaran')
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
