<?php

namespace App\Services;

use App\Models\{LaporanKeuangan, Perusahaan};
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class LaporanKeuanganService
{
    public function getLaporanData(Carbon $startDate, Carbon $endDate)
    {
        $query = LaporanKeuangan::query()
            ->whereBetween('tanggal', [$startDate->startOfDay(), $endDate->endOfDay()]);

        // 1. Transaksi Saldo (dari penambahan saldo manual)
        $transaksiSaldo = $query->clone()
            ->where('kategori', 'Saldo')
            ->orderBy('tanggal')
            ->get();

        // 2. Transaksi DO
        $transaksiDo = $query->clone()
            ->where('kategori', 'DO')
            ->where('sumber_transaksi', 'DO')
            ->orderBy('tanggal')
            ->get();

        // 3. Transaksi Operasional
        $transaksiOperasional = $query->clone()
            ->where('kategori', 'Operasional')
            ->where('sumber_transaksi', 'Operasional')
            ->orderBy('tanggal')
            ->get();

        // Calculate summaries
        $summaries = $this->calculateSummaries(
            $startDate,
            $transaksiSaldo,
            $transaksiDo,
            $transaksiOperasional
        );

        return array_merge([
            'transaksiSaldo' => $transaksiSaldo,
            'transaksiDo' => $transaksiDo,
            'transaksiOperasional' => $transaksiOperasional,
        ], $summaries);
    }

    private function calculateSummaries(
        Carbon $startDate,
        $transaksiSaldo,
        $transaksiDo,
        $transaksiOperasional
    ) {
        // Total perubahan saldo dari penambahan manual
        $totalPerubahanSaldo = $transaksiSaldo->sum(function ($t) {
            return $t->jenis_transaksi === 'Pemasukan' ? $t->nominal : -$t->nominal;
        });

        // Total dari DO
        $totalPemasukanDo = $transaksiDo
            ->where('jenis_transaksi', 'Pemasukan')
            ->sum('nominal');
        $totalPengeluaranDo = $transaksiDo
            ->where('jenis_transaksi', 'Pengeluaran')
            ->sum('nominal');
        $totalTransaksiDo = $totalPemasukanDo - $totalPengeluaranDo;

        // Total dari Operasional
        $totalPemasukanOps = $transaksiOperasional
            ->where('jenis_transaksi', 'Pemasukan')
            ->sum('nominal');
        $totalPengeluaranOps = $transaksiOperasional
            ->where('jenis_transaksi', 'Pengeluaran')
            ->sum('nominal');
        $totalOperasional = $totalPemasukanOps - $totalPengeluaranOps;

        // Get saldo awal
        $saldoAwal = $this->getSaldoAwal($startDate);

        // Calculate saldo akhir
        $saldoAkhir = $saldoAwal + $totalPerubahanSaldo + $totalTransaksiDo + $totalOperasional;

        return [
            'totalPerubahanSaldo' => $totalPerubahanSaldo,
            'totalPemasukanDo' => $totalPemasukanDo,
            'totalPengeluaranDo' => $totalPengeluaranDo,
            'totalTransaksiDo' => $totalTransaksiDo,
            'totalPemasukanOps' => $totalPemasukanOps,
            'totalPengeluaranOps' => $totalPengeluaranOps,
            'totalOperasional' => $totalOperasional,
            'saldoAwal' => $saldoAwal,
            'saldoAkhir' => $saldoAkhir
        ];
    }

    private function getSaldoAwal(Carbon $startDate)
    {
        // Cari saldo dari transaksi terakhir sebelum startDate
        $lastTransaction = LaporanKeuangan::where('tanggal', '<', $startDate->startOfDay())
            ->orderBy('tanggal', 'desc')
            ->first();

        if ($lastTransaction) {
            return $lastTransaction->saldo_sesudah ?? 0;
        }

        // Jika tidak ada transaksi sebelumnya, ambil dari saldo perusahaan
        $perusahaan = Perusahaan::first();
        return $perusahaan ? $perusahaan->saldo : 0;
    }
}
