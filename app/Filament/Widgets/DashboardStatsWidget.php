<?php

namespace App\Filament\Widgets;

use App\Models\{Perusahaan, Penjual, TransaksiDo, LaporanKeuangan};
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardStatsWidget extends BaseWidget
{
    protected static bool $shouldRegister = false;
    protected static ?int $sort = 1;
    protected int | string | array $columnSpan = 'full';

    public function getStats(): array
    {
        $startOfMonth = now()->startOfMonth();
        $endOfMonth = now()->endOfMonth();

        // Calculate income using same logic as TransaksiDoStatWidget
        $incomingFunds = DB::table('transaksi_do')
            ->whereNull('deleted_at')
            ->whereBetween('tanggal', [$startOfMonth, $endOfMonth])
            ->select([
                DB::raw('COALESCE(SUM(pembayaran_hutang), 0) as total_debt_payments'),
                DB::raw('COALESCE(SUM(CASE
                    WHEN cara_bayar IN ("transfer", "cair di luar", "belum dibayar")
                    THEN sisa_bayar
                    ELSE 0
                END), 0) as remaining_payments')
            ])->first();

        $operationalIncome = DB::table('operasional')
            ->whereNull('deleted_at')
            ->where('operasional', 'pemasukan')
            ->whereBetween('tanggal', [$startOfMonth, $endOfMonth])
            ->sum('nominal');

        // Calculate total monthly income
        $monthlyIncome = $incomingFunds->total_debt_payments + 
            $incomingFunds->remaining_payments + 
            $operationalIncome;

        // Calculate expenses
        $monthlyDOExpense = DB::table('transaksi_do')
            ->whereNull('deleted_at')
            ->whereBetween('tanggal', [$startOfMonth, $endOfMonth])
            ->sum('sub_total');

        $monthlyOperationalExpense = DB::table('operasional')
            ->whereNull('deleted_at')
            ->where('operasional', 'pengeluaran')
            ->whereBetween('tanggal', [$startOfMonth, $endOfMonth])
            ->sum('nominal');

        $monthlyExpense = $monthlyDOExpense + $monthlyOperationalExpense;

        // Calculate monthly profit
        $monthlyProfit = $monthlyIncome - $monthlyExpense;

        // Format date range
        $dateRange = "Periode: {$startOfMonth->format('d M Y')} - {$endOfMonth->format('d M Y')}";

        return [
            Stat::make('Pemasukan Bulan Ini', 'Rp ' . number_format($monthlyIncome, 0, ',', '.'))
                ->description(sprintf(
                    "Hutang: Rp %s\nSisa: Rp %s\nOperasional: Rp %s",
                    number_format($incomingFunds->total_debt_payments, 0, ',', '.'),
                    number_format($incomingFunds->remaining_payments, 0, ',', '.'),
                    number_format($operationalIncome, 0, ',', '.')
                ))
                ->icon('heroicon-m-arrow-trending-up')
                ->color('success'),

            Stat::make('Pengeluaran Bulan Ini', 'Rp ' . number_format($monthlyExpense, 0, ',', '.'))
                ->description(sprintf(
                    "DO: Rp %s\nOperasional: Rp %s",
                    number_format($monthlyDOExpense, 0, ',', '.'),
                    number_format($monthlyOperationalExpense, 0, ',', '.')
                ))
                ->icon('heroicon-m-arrow-trending-down')
                ->color('danger'),

            Stat::make('Keuntungan Bulan Ini', 'Rp ' . number_format(abs($monthlyProfit), 0, ',', '.'))
                ->description($monthlyProfit >= 0 ? 'Profit' : 'Rugi')
                ->icon($monthlyProfit >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($monthlyProfit >= 0 ? 'success' : 'danger'),

            Stat::make('Total Transaksi', TransaksiDo::whereBetween('tanggal', [$startOfMonth, $endOfMonth])->count())
                ->description($dateRange)
                ->icon('heroicon-m-document-text')
                ->color('primary'),
        ];
    }
}
