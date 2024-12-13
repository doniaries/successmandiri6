<?php

namespace App\Filament\Widgets;

use App\Models\{TransaksiDo, Operasional};
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class MonthlyFinanceChartWidget extends ChartWidget
{
    protected static ?string $heading = 'Grafik Keuangan';
    protected static ?int $sort = 2;
    protected int | string | array $columnSpan = 'full';
    public ?string $filter = 'monthly';

    protected function getFilters(): ?array
    {
        return [
            'daily' => 'Harian',
            'monthly' => 'Bulanan',
        ];
    }

    protected function getData(): array
    {
        if ($this->filter === 'daily') {
            return $this->getDailyData();
        }

        return $this->getMonthlyData();
    }

    protected function getDailyData(): array
    {
        $days = collect(range(1, Carbon::now()->daysInMonth))->map(function ($day) {
            return Carbon::now()->startOfMonth()->addDays($day - 1);
        });

        $dailyData = $days->map(function ($date) {
            // Get DO income
            $doIncome = DB::table('transaksi_do')
                ->whereNull('deleted_at')
                ->whereDate('tanggal', $date)
                ->select([
                    DB::raw('COALESCE(SUM(pembayaran_hutang), 0) as debt_payments'),
                    DB::raw('COALESCE(SUM(CASE
                        WHEN cara_bayar IN ("transfer", "cair di luar", "belum dibayar")
                        THEN sisa_bayar
                        ELSE 0
                    END), 0) as remaining_payments')
                ])->first();

            // Get operational income
            $operationalIncome = DB::table('operasional')
                ->whereNull('deleted_at')
                ->whereDate('tanggal', $date)
                ->where('operasional', 'pemasukan')
                ->sum('nominal');

            // Get expenses
            $doExpense = DB::table('transaksi_do')
                ->whereNull('deleted_at')
                ->whereDate('tanggal', $date)
                ->sum('sub_total');

            $operationalExpense = DB::table('operasional')
                ->whereNull('deleted_at')
                ->whereDate('tanggal', $date)
                ->where('operasional', 'pengeluaran')
                ->sum('nominal');

            $totalIncome = $doIncome->debt_payments + $doIncome->remaining_payments + $operationalIncome;
            $totalExpense = $doExpense + $operationalExpense;
            $profit = $totalIncome - $totalExpense;

            return [
                'date' => $date->format('d M'),
                'income' => $totalIncome,
                'expense' => $totalExpense,
                'profit' => $profit,
            ];
        });

        return [
            'datasets' => [
                [
                    'label' => 'Pemasukan',
                    'data' => $dailyData->pluck('income')->toArray(),
                    'borderColor' => '#10B981',
                    'backgroundColor' => '#10B981',
                ],
                [
                    'label' => 'Pengeluaran',
                    'data' => $dailyData->pluck('expense')->toArray(),
                    'borderColor' => '#EF4444',
                    'backgroundColor' => '#EF4444',
                ],
                [
                    'label' => 'Keuntungan',
                    'data' => $dailyData->pluck('profit')->toArray(),
                    'borderColor' => '#3B82F6',
                    'backgroundColor' => '#3B82F6',
                ],
            ],
            'labels' => $dailyData->pluck('date')->toArray(),
        ];
    }

    protected function getMonthlyData(): array
    {
        $months = collect(range(1, 12))->map(function ($month) {
            return Carbon::now()->startOfYear()->addMonths($month - 1);
        });

        $monthlyData = $months->map(function ($date) {
            $startOfMonth = $date->copy()->startOfMonth();
            $endOfMonth = $date->copy()->endOfMonth();

            // Get DO income
            $doIncome = DB::table('transaksi_do')
                ->whereNull('deleted_at')
                ->whereBetween('tanggal', [$startOfMonth, $endOfMonth])
                ->select([
                    DB::raw('COALESCE(SUM(pembayaran_hutang), 0) as debt_payments'),
                    DB::raw('COALESCE(SUM(CASE
                        WHEN cara_bayar IN ("transfer", "cair di luar", "belum dibayar")
                        THEN sisa_bayar
                        ELSE 0
                    END), 0) as remaining_payments')
                ])->first();

            // Get operational income
            $operationalIncome = DB::table('operasional')
                ->whereNull('deleted_at')
                ->whereBetween('tanggal', [$startOfMonth, $endOfMonth])
                ->where('operasional', 'pemasukan')
                ->sum('nominal');

            // Get expenses
            $doExpense = DB::table('transaksi_do')
                ->whereNull('deleted_at')
                ->whereBetween('tanggal', [$startOfMonth, $endOfMonth])
                ->sum('sub_total');

            $operationalExpense = DB::table('operasional')
                ->whereNull('deleted_at')
                ->whereBetween('tanggal', [$startOfMonth, $endOfMonth])
                ->where('operasional', 'pengeluaran')
                ->sum('nominal');

            $totalIncome = $doIncome->debt_payments + $doIncome->remaining_payments + $operationalIncome;
            $totalExpense = $doExpense + $operationalExpense;
            $profit = $totalIncome - $totalExpense;

            return [
                'date' => $date->format('M Y'),
                'income' => $totalIncome,
                'expense' => $totalExpense,
                'profit' => $profit,
            ];
        });

        return [
            'datasets' => [
                [
                    'label' => 'Pemasukan',
                    'data' => $monthlyData->pluck('income')->toArray(),
                    'borderColor' => '#10B981',
                    'backgroundColor' => '#10B981',
                ],
                [
                    'label' => 'Pengeluaran',
                    'data' => $monthlyData->pluck('expense')->toArray(),
                    'borderColor' => '#EF4444',
                    'backgroundColor' => '#EF4444',
                ],
                [
                    'label' => 'Keuntungan',
                    'data' => $monthlyData->pluck('profit')->toArray(),
                    'borderColor' => '#3B82F6',
                    'backgroundColor' => '#3B82F6',
                ],
            ],
            'labels' => $monthlyData->pluck('date')->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
