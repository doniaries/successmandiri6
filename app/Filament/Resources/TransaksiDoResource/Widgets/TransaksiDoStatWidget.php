<?php

namespace App\Filament\Resources\TransaksiDoResource\Widgets;

use App\Models\TransaksiDo;
use App\Models\Perusahaan;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class TransaksiDoStatWidget extends BaseWidget
{
    // Konfigurasi widget
    // protected static ?string $heading = 'Ringkasan Transaksi Hari Ini';
    protected static ?int $sort = 1;
    protected static ?string $pollingInterval = '15s';

    // Lazy loading untuk performa
    protected static bool $isLazy = true;

    // Method untuk menggantikan properti heading
    public function getHeading(): ?string
    {
        return 'Ringkasan Transaksi Hari Ini';
    }



    //update stats saldo
    #[On(['refresh-widgets', 'saldo-updated'])]
    public function refresh(): void
    {
        $this->getFilteredStats();
    }

    public function getStats(): array
    {
        try {
            // Get perusahaan dengan eager loading yang optimal
            $perusahaan = Perusahaan::select('id', 'name', 'saldo')->first();
            if (!$perusahaan) {
                return $this->getErrorStats('Data perusahaan tidak ditemukan');
            }

            // Get statistik
            $stats = $this->getTransaksiStats();

            return [
                // Saldo Stats
                Stat::make('Saldo Kas', 'Rp ' . number_format($perusahaan->saldo, 0, ',', '.'))
                    ->description('Update otomatis setiap 15 detik')
                    ->descriptionIcon('heroicon-m-arrow-path')
                    ->color('success'),

                // Transaction Stats
                Stat::make('Total Transaksi DO', "{$stats['total_transaksi']} DO")
                    ->description(sprintf(
                        "Total Tonase: %s Kg",
                        number_format($stats['total_tonase'], 0, ',', '.')
                    ))
                    ->descriptionIcon('heroicon-m-truck')
                    ->color('info'),

                // Income Stats
                Stat::make(
                    'Total Pemasukan',
                    'Rp ' . number_format($stats['total_pemasukan'], 0, ',', '.')
                )
                    ->description($this->formatPemasukanDescription($stats))
                    ->descriptionIcon('heroicon-m-arrow-trending-up')
                    ->color('success'),

                // Expense Stats
                Stat::make(
                    'Total Pengeluaran',
                    'Rp ' . number_format($stats['total_pengeluaran'], 0, ',', '.')
                )
                    ->description($this->formatPengeluaranDescription($stats))
                    ->descriptionIcon('heroicon-m-arrow-trending-down')
                    ->color('danger'),
            ];
        } catch (\Exception $e) {
            \Log::error('Error in TransaksiDoStatWidget:', [
                'error' => $e->getMessage()
            ]);

            return $this->getErrorStats('Terjadi kesalahan saat memuat data');
        }
    }

    protected function getTransaksiStats(): array
    {
        // Optimasi: Gunakan collection get() dan langsung transform ke array
        return DB::table('transaksi_do')
            ->whereDate('tanggal', Carbon::today())
            ->select([
                DB::raw('COUNT(*) as total_transaksi'),
                DB::raw('COALESCE(SUM(tonase), 0) as total_tonase'),
                DB::raw('COALESCE(SUM(upah_bongkar), 0) as upah_bongkar'),
                DB::raw('COALESCE(SUM(biaya_lain), 0) as biaya_lain'),
                DB::raw('COALESCE(SUM(pembayaran_hutang), 0) as pembayaran_hutang'),
                DB::raw('COALESCE(SUM(upah_bongkar + biaya_lain + pembayaran_hutang), 0) as total_pemasukan'),
                // Update query untuk cara bayar
                DB::raw('COALESCE(SUM(CASE WHEN cara_bayar = "Tunai" THEN sisa_bayar ELSE 0 END), 0) as total_tunai'),
                DB::raw('COALESCE(SUM(CASE WHEN cara_bayar = "Transfer" THEN sisa_bayar ELSE 0 END), 0) as total_transfer'),
                DB::raw('COALESCE(SUM(CASE WHEN cara_bayar = "Cair di Luar" THEN sisa_bayar ELSE 0 END), 0) as total_cair_di_luar'),
                DB::raw('COALESCE(SUM(sisa_bayar), 0) as total_pengeluaran'),
                DB::raw('COUNT(CASE WHEN cara_bayar = "Tunai" THEN 1 END) as tunai_count'),
                DB::raw('COUNT(CASE WHEN cara_bayar = "Transfer" THEN 1 END) as transfer_count'),
                DB::raw('COUNT(CASE WHEN cara_bayar = "Cair di Luar" THEN 1 END) as cair_di_luar_count'),
            ])
            ->get()
            ->map(function ($item) {
                return (array) $item;
            })
            ->first();
    }

    protected function formatPemasukanDescription(array $stats): string
    {
        $components = [];

        if ($stats['upah_bongkar'] > 0) {
            $components[] = sprintf(
                "Upah Bongkar: Rp %s",
                number_format($stats['upah_bongkar'], 0, ',', '.')
            );
        }

        if ($stats['biaya_lain'] > 0) {
            $components[] = sprintf(
                "Biaya Lain: Rp %s",
                number_format($stats['biaya_lain'], 0, ',', '.')
            );
        }

        if ($stats['pembayaran_hutang'] > 0) {
            $components[] = sprintf(
                "Bayar Hutang: Rp %s",
                number_format($stats['pembayaran_hutang'], 0, ',', '.')
            );
        }

        return empty($components) ? 'Belum ada pemasukan' : implode("\n", $components);
    }

    protected function formatPengeluaranDescription(array $stats): string
    {
        $components = [];

        if ($stats['total_tunai'] > 0) {
            $components[] = sprintf(
                "Tunai (%d DO): Rp %s",
                $stats['tunai_count'],
                number_format($stats['total_tunai'], 0, ',', '.')
            );
        }

        if ($stats['total_transfer'] > 0) {
            $components[] = sprintf(
                "Transfer (%d DO): Rp %s",
                $stats['transfer_count'],
                number_format($stats['total_transfer'], 0, ',', '.')
            );
        }

        if ($stats['total_cair_di_luar'] > 0) {
            $components[] = sprintf(
                "Cair di Luar (%d DO): Rp %s",
                $stats['cair_di_luar_count'],
                number_format($stats['total_cair_di_luar'], 0, ',', '.')
            );
        }

        return empty($components) ? 'Belum ada pengeluaran' : implode("\n", $components);
    }

    protected function getErrorStats(string $message): array
    {
        return [
            Stat::make('Error', $message)
                ->description('Terjadi kesalahan')
                ->descriptionIcon('heroicon-m-exclamation-triangle')
                ->color('danger')
        ];
    }
}
