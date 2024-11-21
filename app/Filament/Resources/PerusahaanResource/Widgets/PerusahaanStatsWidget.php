<?php

namespace App\Filament\Resources\PerusahaanResource\Widgets;

use App\Models\Perusahaan;
use Carbon\Carbon;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;


class PerusahaanStatsWidget extends BaseWidget
{
    protected static ?string $pollingInterval = '15s';
    protected int | string | array $columnSpan = 'full';

    public function getStats(): array
    {
        $perusahaan = Perusahaan::first();

        // Get today's saldo mutations
        $today = Carbon::today();
        $mutations = DB::table('laporan_keuangan')
            ->whereDate('tanggal', $today)
            ->select([
                DB::raw('COALESCE(SUM(CASE WHEN jenis_transaksi = "Pemasukan" THEN nominal ELSE 0 END), 0) as total_in'),
                DB::raw('COALESCE(SUM(CASE WHEN jenis_transaksi = "Pengeluaran" THEN nominal ELSE 0 END), 0) as total_out'),
                DB::raw('COUNT(*) as total_transactions')
            ])
            ->first();

        return [
            Stat::make('Saldo Perusahaan', 'Rp ' . number_format($perusahaan?->saldo ?? 0, 0, ',', '.'))
                ->description('Saldo terkini')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color($this->getSaldoColor($perusahaan?->saldo ?? 0)),

            Stat::make('Mutasi Hari Ini', "Rp " . number_format(abs($mutations->total_in - $mutations->total_out), 0, ',', '.'))
                ->description(sprintf(
                    "IN: Rp %s\nOUT: Rp %s",
                    number_format($mutations->total_in, 0, ',', '.'),
                    number_format($mutations->total_out, 0, ',', '.')
                ))
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('success'),

            Stat::make('Total Transaksi', "{$mutations->total_transactions} transaksi")
                ->description('Update otomatis setiap 15 detik')
                ->descriptionIcon('heroicon-m-arrow-path')
                ->color('info'),
        ];
    }

    private function getSaldoColor($saldo): string
    {
        return match (true) {
            $saldo > 1000000000 => 'success', // > 1M
            $saldo > 100000000 => 'info',     // > 100jt
            $saldo > 0 => 'warning',          // > 0
            default => 'danger'               // <= 0
        };
    }
}
