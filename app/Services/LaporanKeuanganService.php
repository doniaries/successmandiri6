<?php

namespace App\Services;

use Carbon\Carbon;
use App\Models\{TransaksiDo, Operasional, Perusahaan, LaporanKeuangan};
use Illuminate\Support\Facades\DB;

class LaporanKeuanganService
{
    public function getLaporanData($startDate, $endDate)
    {
        // Get initial balance from perusahaan
        $perusahaan = Perusahaan::first();
        $saldoAwal = $perusahaan->saldo ?? 0;

        // Get DO transactions for the day
        $transaksiDo = TransaksiDo::with(['penjual', 'supir'])
            ->whereDate('tanggal', Carbon::parse($startDate)->toDateString())
            ->orderBy('tanggal', 'asc')
            ->get();

        // Get operational transactions
        $transaksiOperasional = Operasional::whereDate('tanggal', Carbon::parse($startDate)->toDateString())
            ->orderBy('tanggal', 'asc')
            ->get();

        // Get all transactions in date range
        $transaksi = LaporanKeuangan::whereBetween('tanggal', [$startDate, $endDate])
            ->orderBy('tanggal', 'asc')
            ->get();

        // Calculate totals by category
        $pemasukan = $transaksi->where('jenis_transaksi', 'Pemasukan');
        $pengeluaran = $transaksi->where('jenis_transaksi', 'Pengeluaran');

        // Group transactions
        $transaksiSaldo = $transaksi->where('kategori', 'Saldo');
        $transaksiDoLaporan = $transaksi->where('kategori', 'DO');
        $transaksiOperasionalLaporan = $transaksi->where('kategori', 'Operasional');

        // Calculate totals
        $totalTonase = $transaksiDo->sum('tonase');
        $totalSubTotal = $transaksiDo->sum('sub_total');
        $totalBiaya = $transaksiDo->sum(function($do) {
            return $do->biaya_lain + $do->upah_bongkar;
        });
        $totalBayarHutang = $transaksiDo->sum('pembayaran_hutang');

        $totalTunai = $transaksiDo->where('cara_bayar', 'Tunai')->sum('sisa_bayar');
        $totalTransfer = $transaksiDo->where('cara_bayar', 'Transfer')->sum('sisa_bayar');
        $totalCairDiluar = $transaksiDo->where('cara_bayar', 'cair di luar')->sum('sisa_bayar');
        $totalBelumBayar = $transaksiDo->sum('sisa_hutang_penjual');

        $totalPerubahanSaldo = $transaksiSaldo->sum(function ($item) {
            return $item->jenis_transaksi === 'Pemasukan' ? $item->nominal : -$item->nominal;
        });

        $totalTransaksiDoLaporan = $transaksiDoLaporan->sum(function ($item) {
            return $item->jenis_transaksi === 'Pemasukan' ? $item->nominal : -$item->nominal;
        });

        $totalOperasionalLaporan = $transaksiOperasionalLaporan->sum(function ($item) {
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
            'total_do' => $totalTransaksiDoLaporan,
            'total_operasional' => $totalOperasionalLaporan,
            'saldo_akhir' => $saldoAkhir
        ]);

        return [
            'perusahaan' => $perusahaan,
            'transaksiDo' => $transaksiDo,
            'transaksiOperasional' => $transaksiOperasional,
            'transaksiSaldo' => $transaksiSaldo,
            'transaksiDoLaporan' => $transaksiDoLaporan,
            'transaksiOperasionalLaporan' => $transaksiOperasionalLaporan,
            'totalTonase' => $totalTonase,
            'totalSubTotal' => $totalSubTotal,
            'totalBiaya' => $totalBiaya,
            'totalBayarHutang' => $totalBayarHutang,
            'totalTunai' => $totalTunai,
            'totalTransfer' => $totalTransfer,
            'totalCairDiluar' => $totalCairDiluar,
            'totalBelumBayar' => $totalBelumBayar,
            'totalPerubahanSaldo' => $totalPerubahanSaldo,
            'totalTransaksiDoLaporan' => $totalTransaksiDoLaporan,
            'totalOperasionalLaporan' => $totalOperasionalLaporan,
            'totalPemasukan' => $totalPemasukan,
            'totalPengeluaran' => $totalPengeluaran,
            'saldoAkhir' => $saldoAkhir,
            'detailPemasukan' => [
                'saldo' => $transaksiSaldo->where('jenis_transaksi', 'Pemasukan')->sum('nominal'),
                'do' => $transaksiDoLaporan->where('jenis_transaksi', 'Pemasukan')->sum('nominal'),
                'operasional' => $transaksiOperasionalLaporan->where('jenis_transaksi', 'Pemasukan')->sum('nominal'),
            ],
            'detailPengeluaran' => [
                'saldo' => $transaksiSaldo->where('jenis_transaksi', 'Pengeluaran')->sum('nominal'),
                'do' => $transaksiDoLaporan->where('jenis_transaksi', 'Pengeluaran')->sum('nominal'),
                'operasional' => $transaksiOperasionalLaporan->where('jenis_transaksi', 'Pengeluaran')->sum('nominal'),
            ]
        ];
    }
}
