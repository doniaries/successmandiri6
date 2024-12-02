<?php

namespace App\Filament\Resources\TransaksiDoResource\Widgets;

use App\Models\{TransaksiDo, Perusahaan};
use Filament\Widgets\StatsOverviewWidget\Stat;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Illuminate\Support\Facades\{DB, Log, Cache};
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
            $perusahaan = Cache::remember('perusahaan-data', 5, function () {
                return Perusahaan::first();
            });

            // Pengeluaran dari Transaksi DO & Operasional
            $pengeluaranPerCaraBayar = DB::table('laporan_keuangan')
                ->whereNull('deleted_at')
                ->where('jenis_transaksi', 'Pengeluaran')
                ->select([
                    'cara_pembayaran',
                    DB::raw('SUM(nominal) as total'),
                    DB::raw('COUNT(DISTINCT id) as jumlah')
                ])
                ->groupBy('cara_pembayaran')
                ->get()
                ->keyBy('cara_pembayaran');

            // Total Pemasukan dari DO
            $pemasukanDO = DB::table('transaksi_do')
                ->whereNull('deleted_at')
                ->select([
                    DB::raw('SUM(total) as total_do'),
                    DB::raw('COUNT(id) as jumlah_do')
                ])
                ->first();

            // Total Pemasukan dari Operasional
            $pemasukanOperasional = DB::table('operasional')
                ->whereNull('deleted_at')
                ->where('operasional', 'pemasukan')
                ->select([
                    DB::raw('SUM(nominal) as total_operasional'),
                    DB::raw('COUNT(id) as jumlah_operasional')
                ])
                ->first();

            // Total semua pengeluaran (DO + Operasional)
            $totalPengeluaran = DB::table('laporan_keuangan')
                ->whereNull('deleted_at')
                ->where('jenis_transaksi', 'Pengeluaran')
                ->select([
                    'kategori',
                    DB::raw('SUM(nominal) as total'),
                    DB::raw('COUNT(DISTINCT id) as jumlah')
                ])
                ->groupBy('kategori')
                ->get()
                ->keyBy('kategori');

            return [
                // Saldo Kas
                Stat::make('Saldo Kas', 'Rp ' . number_format($perusahaan->saldo, 0, ',', '.'))
                    ->description('Total saldo tersedia')
                    ->descriptionIcon('heroicon-m-banknotes')
                    ->color('success'),

                // Total Pengeluaran dengan rincian kategori
                Stat::make(
                    'Total Pengeluaran',
                    'Rp ' . number_format(
                        ($totalPengeluaran['DO']->total ?? 0) + ($totalPengeluaran['Operasional']->total ?? 0),
                        0,
                        ',',
                        '.'
                    )
                )
                    ->description(sprintf(
                        "Total %d Transaksi\nDO: Rp %s (%d)\nOperasional: Rp %s (%d)",
                        ($totalPengeluaran['DO']->jumlah ?? 0) + ($totalPengeluaran['Operasional']->jumlah ?? 0),
                        number_format($totalPengeluaran['DO']->total ?? 0, 0, ',', '.'),
                        $totalPengeluaran['DO']->jumlah ?? 0,
                        number_format($totalPengeluaran['Operasional']->total ?? 0, 0, ',', '.'),
                        $totalPengeluaran['Operasional']->jumlah ?? 0
                    ))
                    ->descriptionIcon('heroicon-m-arrow-trending-down')
                    ->color('danger'),

                // Total Pemasukan dari DO dan Operasional
                Stat::make(
                    'Total Pemasukan',
                    'Rp ' . number_format(
                        ($pemasukanDO->total_do ?? 0) + ($pemasukanOperasional->total_operasional ?? 0),
                        0,
                        ',',
                        '.'
                    )
                )
                    ->description(sprintf(
                        "Tunai: Rp %s (%d)\nTransfer: Rp %s (%d)\nCair di Luar: Rp %s (%d)",
                        number_format($pengeluaranPerCaraBayar['Tunai']->total ?? 0, 0, ',', '.'),
                        $pengeluaranPerCaraBayar['Tunai']->jumlah ?? 0,
                        number_format($pengeluaranPerCaraBayar['Transfer']->total ?? 0, 0, ',', '.'),
                        $pengeluaranPerCaraBayar['Transfer']->jumlah ?? 0,
                        number_format($pengeluaranPerCaraBayar['cair di luar']->total ?? 0, 0, ',', '.'),
                        $pengeluaranPerCaraBayar['cair di luar']->jumlah ?? 0
                    ))
                    ->descriptionIcon('heroicon-m-arrow-trending-up')
                    ->color('success')
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

    #[On(['refresh-widget', 'transaksi-created', 'transaksi-updated', 'transaksi-deleted', 'saldo-updated'])]
    public function refresh(): void
    {
        Cache::forget('transaksi-stats');
        Cache::forget('perusahaan-data');
    }
}
