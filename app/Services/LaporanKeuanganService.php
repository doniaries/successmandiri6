<?php

namespace App\Services;

use Carbon\Carbon;
use App\Models\{LaporanKeuangan, Perusahaan};
use Illuminate\Support\Facades\DB;

class LaporanKeuanganService
{
    public function getLaporanData($startDate, $endDate)
    {
        // Get initial balance from perusahaan
        $perusahaan = Perusahaan::first();
        $saldoAwal = $perusahaan->saldo ?? 0;

        // Get all transactions in date range
        $transaksi = LaporanKeuangan::whereBetween('tanggal', [$startDate, $endDate])
            ->orderBy('tanggal', 'asc')
            ->get();

        // Calculate totals by category
        $pemasukan = $transaksi->where('jenis_transaksi', 'Pemasukan');
        $pengeluaran = $transaksi->where('jenis_transaksi', 'Pengeluaran');

        // Group transactions
        $transaksiSaldo = $transaksi->where('kategori', 'Saldo');
        $transaksiDo = $transaksi->where('kategori', 'DO');
        $transaksiOperasional = $transaksi->where('kategori', 'Operasional');

        // Calculate totals
        $totalPerubahanSaldo = $transaksiSaldo->sum(function ($item) {
            return $item->jenis_transaksi === 'Pemasukan' ? $item->nominal : -$item->nominal;
        });

        $totalTransaksiDo = $transaksiDo->sum(function ($item) {
            return $item->jenis_transaksi === 'Pemasukan' ? $item->nominal : -$item->nominal;
        });

        $totalOperasional = $transaksiOperasional->sum(function ($item) {
            return $item->jenis_transaksi === 'Pemasukan' ? $item->nominal : -$item->nominal;
        });

        // Calculate final balance
        $totalPemasukan = $pemasukan->sum('nominal');
        $totalPengeluaran = $pengeluaran->sum('nominal');
        $saldoAkhir = $saldoAwal + $totalPemasukan - $totalPengeluaran;

        // Log calculations for debugging
        \Log::info('Laporan Calculations', [
            'start_date' => $startDate,
            'end_date' => $endDate,
            'saldo_awal' => $saldoAwal,
            'total_pemasukan' => $totalPemasukan,
            'total_pengeluaran' => $totalPengeluaran,
            'total_saldo' => $totalPerubahanSaldo,
            'total_do' => $totalTransaksiDo,
            'total_operasional' => $totalOperasional,
            'saldo_akhir' => $saldoAkhir
        ]);

        return [
            'transaksiSaldo' => $transaksiSaldo,
            'transaksiDo' => $transaksiDo,
            'transaksiOperasional' => $transaksiOperasional,
            'saldoAwal' => $saldoAwal,
            'totalPerubahanSaldo' => $totalPerubahanSaldo,
            'totalTransaksiDo' => $totalTransaksiDo,
            'totalOperasional' => $totalOperasional,
            'totalPemasukan' => $totalPemasukan,
            'totalPengeluaran' => $totalPengeluaran,
            'saldoAkhir' => $saldoAkhir,
            'detailPemasukan' => [
                'saldo' => $transaksiSaldo->where('jenis_transaksi', 'Pemasukan')->sum('nominal'),
                'do' => $transaksiDo->where('jenis_transaksi', 'Pemasukan')->sum('nominal'),
                'operasional' => $transaksiOperasional->where('jenis_transaksi', 'Pemasukan')->sum('nominal'),
            ],
            'detailPengeluaran' => [
                'saldo' => $transaksiSaldo->where('jenis_transaksi', 'Pengeluaran')->sum('nominal'),
                'do' => $transaksiDo->where('jenis_transaksi', 'Pengeluaran')->sum('nominal'),
                'operasional' => $transaksiOperasional->where('jenis_transaksi', 'Pengeluaran')->sum('nominal'),
            ]
        ];
    }
}
