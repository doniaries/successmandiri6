<?php

namespace App\Filament\Resources\TransaksiDoResource\Widgets;

use App\Models\{TransaksiDo, Perusahaan};
use Filament\Widgets\StatsOverviewWidget\Stat;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Carbon\Carbon;
use Illuminate\Support\Facades\{DB, Log};
use Livewire\Attributes\On;

class TransaksiDoStatWidget extends BaseWidget
{
    protected static ?int $sort = 1;
    protected static ?string $pollingInterval = '5s';
    protected static bool $isLazy = true;
    protected int | string | array $columnSpan = 'full';

    protected function getStats(): array
    {
        try {
            // Ambil data perusahaan pertama (CV SUCCESS MANDIRI)
            $perusahaan = DB::table('perusahaans')
                ->whereNull('deleted_at')
                ->where('id', 1)
                ->select('id', 'name', 'saldo')
                ->first();

            if (!$perusahaan) {
                throw new \Exception('Data perusahaan tidak ditemukan');
            }

            // Total dari laporan keuangan (pemasukan)
            $laporanPemasukan = DB::table('laporan_keuangan')
                ->whereNull('deleted_at')
                ->where('jenis_transaksi', 'Pemasukan')
                ->where(function ($query) {
                    $query->where('cara_pembayaran', 'Transfer')
                        ->orWhere('kategori', 'DO')
                        ->orWhere('sub_kategori', 'Bayar Hutang');
                })
                ->sum('nominal');

            $totalSaldoMasuk = $perusahaan->saldo + $laporanPemasukan;

            // Total pengeluaran dari laporan keuangan
            $totalPengeluaran = DB::table('laporan_keuangan')
                ->whereNull('deleted_at')
                ->where('jenis_transaksi', 'Pengeluaran')
                ->sum('nominal');

            // Sisa Saldo
            $sisaSaldo = $totalSaldoMasuk - $totalPengeluaran;

            // Ambil detail transaksi
            $detailPemasukan = DB::table('laporan_keuangan')
                ->whereNull('deleted_at')
                ->where('jenis_transaksi', 'Pemasukan')
                ->selectRaw('
                    COALESCE(SUM(CASE WHEN cara_pembayaran = "Transfer" THEN nominal ELSE 0 END), 0) as transfer,
                    COALESCE(SUM(CASE WHEN sub_kategori = "Bayar Hutang" THEN nominal ELSE 0 END), 0) as bayar_hutang
                ')
                ->first();

            $detailPengeluaran = DB::table('laporan_keuangan')
                ->whereNull('deleted_at')
                ->where('jenis_transaksi', 'Pengeluaran')
                ->selectRaw('
                    COALESCE(SUM(CASE WHEN kategori = "DO" THEN nominal ELSE 0 END), 0) as transaksi_do,
                    COALESCE(SUM(CASE WHEN kategori = "Operasional" THEN nominal ELSE 0 END), 0) as operasional
                ')
                ->first();

            return [
                // Tambahkan nama perusahaan sebagai heading
                Stat::make('Sisa Saldo ' . $perusahaan->name, 'Rp ' . number_format($sisaSaldo, 0, ',', '.'))
                    ->description('Saldo tersisa setelah dikurangi pengeluaran')
                    ->color($this->getSaldoColor($sisaSaldo))
                    ->extraAttributes(['class' => 'font-bold']),

                Stat::make('Total Saldo/Uang Masuk', 'Rp ' . number_format($totalSaldoMasuk, 0, ',', '.'))
                    ->description(sprintf(
                        "Saldo %s: Rp %s\nTransfer: Rp %s\nBayar Hutang: Rp %s",
                        $perusahaan->name,
                        number_format($perusahaan->saldo, 0, ',', '.'),
                        number_format($detailPemasukan->transfer ?? 0, 0, ',', '.'),
                        number_format($detailPemasukan->bayar_hutang ?? 0, 0, ',', '.')
                    ))
                    ->color('success'),

                Stat::make('Pengeluaran/Uang Keluar', 'Rp ' . number_format($totalPengeluaran, 0, ',', '.'))
                    ->description(sprintf(
                        "Transaksi DO: Rp %s\nOperasional: Rp %s",
                        number_format($detailPengeluaran->transaksi_do ?? 0, 0, ',', '.'),
                        number_format($detailPengeluaran->operasional ?? 0, 0, ',', '.')
                    ))
                    ->color('danger'),
            ];
        } catch (\Exception $e) {
            Log::error('Error TransaksiDoStatWidget:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTrace()
            ]);

            return [
                Stat::make('Error', 'Terjadi kesalahan memuat data')
                    ->description($e->getMessage())
                    ->color('danger')
            ];
        }
    }

    private function getSaldoColor($saldo): string
    {
        return match (true) {
            $saldo > 50000000 => 'success',
            $saldo > 10000000 => 'info',
            $saldo > 0 => 'warning',
            default => 'danger'
        };
    }

    #[On(['refresh-widget', 'transaksi-created', 'transaksi-updated', 'transaksi-deleted', 'saldo-updated'])]
    public function refresh(): void
    {
        $this->getStats();
    }
}
