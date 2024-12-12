<?php

namespace App\Filament\Resources\TransaksiDoResource\Widgets;

use App\Models\{TransaksiDo, Perusahaan, Operasional};
use Filament\Widgets\StatsOverviewWidget\Stat;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Illuminate\Support\Facades\{DB, Log, Cache};
use Livewire\Attributes\On;

class TransaksiDoStatWidget extends BaseWidget
{
    // Widget configuration
    protected static ?int $sort = 1;
    protected static ?string $pollingInterval = '5s';
    protected static bool $isLazy = true;
    protected int | string | array $columnSpan = 'full';

    protected function getStats(): array
    {
        try {
            // Get stats from cache or calculate
            return Cache::remember('transaksi-stats', 60, function () {
                // Calculate total incoming funds (Total Saldo/Uang Masuk):
                // - Sum of debt payments
                // - Sum of remaining payments for specific payment methods
                // - Plus operational income
                $incomingFunds = DB::table('transaksi_do')
                    ->whereNull('deleted_at')
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
                    ->sum('nominal');

                // Update cara bayar untuk konsistensi
                $totalIncoming = $incomingFunds->total_debt_payments +
                    $incomingFunds->remaining_payments +
                    $operationalIncome;

                // Calculate total expenditure (Pengeluaran/Uang Keluar):
                // - Sum of all DO sub_totals
                // - Plus total operational expenses
                $totalDO = DB::table('transaksi_do')
                    ->whereNull('deleted_at')
                    ->sum('sub_total');

                $totalOperational = DB::table('operasional')
                    ->whereNull('deleted_at')
                    ->where('operasional', 'pengeluaran')
                    ->sum('nominal');

                $totalExpenditure = $totalDO + $totalOperational;

                // Calculate remaining balance (Sisa Saldo)
                $remainingBalance = $totalIncoming - $totalExpenditure;

                // Periksa apakah nilai sudah sesuai dengan yang diharapkan
                $expectedRemainingBalance = 134876700;
                $expectedTotalIncoming = 431775680;
                $expectedTotalExpenditure = 296898980;

                if ($remainingBalance !== $expectedRemainingBalance || $totalIncoming !== $expectedTotalIncoming || $totalExpenditure !== $expectedTotalExpenditure) {
                    Log::warning('Nilai tidak sesuai dengan yang diharapkan', [
                        'Sisa Saldo' => $remainingBalance,
                        'Total Saldo/Uang Masuk' => $totalIncoming,
                        'Pengeluaran/Uang Keluar' => $totalExpenditure,
                    ]);
                }

                return [
                    // Remaining Balance
                    Stat::make('Sisa Saldo', 'Rp ' . number_format($remainingBalance, 0, ',', '.'))
                        ->description('Total saldo masuk - Total pengeluaran')
                        ->descriptionIcon('heroicon-m-banknotes')
                        ->color($remainingBalance >= 0 ? 'success' : 'danger'),

                    // Total Income
                    Stat::make('Total Saldo/Uang Masuk', 'Rp ' . number_format($totalIncoming, 0, ',', '.'))
                        ->description(sprintf(
                            "Pembayaran Hutang: Rp %s\nPembayaran Sisa: Rp %s\nPemasukan Operasional: Rp %s",
                            number_format($incomingFunds->total_debt_payments, 0, ',', '.'),
                            number_format($incomingFunds->remaining_payments, 0, ',', '.'),
                            number_format($operationalIncome, 0, ',', '.')
                        ))
                        ->descriptionIcon('heroicon-m-arrow-trending-up')
                        ->color('success'),

                    // Total Expenditure
                    Stat::make('Pengeluaran/Uang Keluar', 'Rp ' . number_format($totalExpenditure, 0, ',', '.'))
                        ->description(sprintf(
                            "Total DO: Rp %s\nTotal Operasional: Rp %s",
                            number_format($totalDO, 0, ',', '.'),
                            number_format($totalOperational, 0, ',', '.')
                        ))
                        ->descriptionIcon('heroicon-m-arrow-trending-down')
                        ->color('danger'),

                    Stat::make('Total Transaksi', TransaksiDo::count())
                        ->description(sprintf(
                            "tunai: %d\ntransfer: %d\ncair di luar: %d\nbelum dibayar: %d",
                            TransaksiDo::where('cara_bayar', 'tunai')->count(),
                            TransaksiDo::where('cara_bayar', 'transfer')->count(),
                            TransaksiDo::where('cara_bayar', 'cair di luar')->count(),
                            TransaksiDo::where('cara_bayar', 'belum dibayar')->count()
                        ))
                        ->descriptionIcon('heroicon-m-document-text')
                        ->color('primary'),
                ];
            });
        } catch (\Exception $e) {
            Log::error('Error in TransaksiDoStatWidget:', [
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

    // Refresh widget on various events
    #[On(['refresh-widget', 'transaksi-created', 'transaksi-updated', 'transaksi-deleted', 'saldo-updated'])]
    public function refresh(): void
    {
        Cache::forget('transaksi-stats');
    }
}
