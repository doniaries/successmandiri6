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

            // Get summary transaksi
            $transaksi = DB::table('laporan_keuangan')
                ->whereNull('deleted_at')
                ->select([
                    // Summary total
                    DB::raw('SUM(CASE WHEN jenis_transaksi = "Pemasukan" THEN nominal ELSE 0 END) as total_pemasukan'),
                    DB::raw('SUM(CASE WHEN jenis_transaksi = "Pengeluaran" THEN nominal ELSE 0 END) as total_pengeluaran'),
                    // Summary per kategori
                    DB::raw('SUM(CASE WHEN kategori = "DO" AND jenis_transaksi = "Pemasukan" THEN nominal ELSE 0 END) as pemasukan_do'),
                    DB::raw('SUM(CASE WHEN kategori = "DO" AND jenis_transaksi = "Pengeluaran" THEN nominal ELSE 0 END) as pengeluaran_do'),
                    DB::raw('SUM(CASE WHEN kategori = "Operasional" AND jenis_transaksi = "Pemasukan" THEN nominal ELSE 0 END) as pemasukan_operasional'),
                    DB::raw('SUM(CASE WHEN kategori = "Operasional" AND jenis_transaksi = "Pengeluaran" THEN nominal ELSE 0 END) as pengeluaran_operasional'),
                    // Summary per cara bayar
                    DB::raw('SUM(CASE WHEN cara_pembayaran = "Tunai" AND jenis_transaksi = "Pengeluaran" THEN nominal ELSE 0 END) as pengeluaran_tunai'),
                    DB::raw('SUM(CASE WHEN cara_pembayaran = "Transfer" AND jenis_transaksi = "Pengeluaran" THEN nominal ELSE 0 END) as pengeluaran_transfer'),
                    DB::raw('SUM(CASE WHEN cara_pembayaran = "cair di luar" AND jenis_transaksi = "Pengeluaran" THEN nominal ELSE 0 END) as pengeluaran_cair_luar'),
                    DB::raw('COUNT(DISTINCT id) as total_transaksi')
                ])
                ->first();

            return [
                // Saldo Kas
                Stat::make('Saldo Kas', 'Rp ' . number_format($perusahaan->saldo, 0, ',', '.'))
                    ->description('Total saldo tersedia')
                    ->descriptionIcon('heroicon-m-banknotes')
                    ->color('success'),

                // Total Transaksi dengan Summary
                Stat::make(
                    'Total Transaksi',
                    'Rp ' . number_format($transaksi->total_pemasukan - $transaksi->total_pengeluaran, 0, ',', '.')
                )
                    ->description(sprintf(
                        "Pemasukan:\nDO: Rp %s\nOperasional: Rp %s\n\nPengeluaran:\nDO: Rp %s\nOperasional: Rp %s",
                        number_format($transaksi->pemasukan_do ?? 0, 0, ',', '.'),
                        number_format($transaksi->pemasukan_operasional ?? 0, 0, ',', '.'),
                        number_format($transaksi->pengeluaran_do ?? 0, 0, ',', '.'),
                        number_format($transaksi->pengeluaran_operasional ?? 0, 0, ',', '.')
                    ))
                    ->descriptionIcon('heroicon-m-chart-bar')
                    ->color('info'),

                // Detail Pengeluaran
                Stat::make(
                    'Detail Pengeluaran',
                    $transaksi->total_transaksi . ' Transaksi'
                )
                    ->description(sprintf(
                        "Tunai: Rp %s\nTransfer: Rp %s\nCair di Luar: Rp %s",
                        number_format($transaksi->pengeluaran_tunai ?? 0, 0, ',', '.'),
                        number_format($transaksi->pengeluaran_transfer ?? 0, 0, ',', '.'),
                        number_format($transaksi->pengeluaran_cair_luar ?? 0, 0, ',', '.')
                    ))
                    ->descriptionIcon('heroicon-m-banknotes')
                    ->color('warning')
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
