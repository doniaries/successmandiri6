<?php

namespace App\Filament\Resources\LaporanKeuanganResource\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Carbon\Carbon;
use App\Models\{LaporanKeuangan, Perusahaan};
use Illuminate\Support\Facades\{DB, Log};
use Livewire\Attributes\On;

class LaporanKeuanganDoStatsWidget extends BaseWidget
{
    // Widget configuration
    protected static ?int $sort = 1;
    protected static ?string $pollingInterval = '15s';
    protected int | string | array $columnSpan = 'full';

    // State untuk filter
    public $startDate;
    public $endDate;
    public $activeTab = 'semua'; // Default tab

    // Initialize default dates on mount
    // Initialize default dates & tab saat mount
    public function mount(): void
    {
        $this->startDate = now()->subDays(30); // Default 30 hari terakhir
        $this->endDate = now();
    }

    //update stats saldo
    #[On(['refresh-widgets', 'saldo-updated'])]
    public function refresh(): void
    {
        $this->getFilteredStats();
    }

    // Method untuk heading widget
    public function getHeading(): ?string
    {
        return 'Ringkasan Laporan Keuangan';
    }

    // Listen untuk event filter
    #[On(['filter-laporan', 'tab-changed'])]
    public function handleFilter($data = []): void
    {
        if (isset($data['startDate'])) {
            $this->startDate = Carbon::parse($data['startDate']);
        }
        if (isset($data['endDate'])) {
            $this->endDate = Carbon::parse($data['endDate']);
        }
        if (isset($data['tab'])) {
            $this->activeTab = $data['tab'];
        }
    }

    // Main stats getter
    protected function getStats(): array
    {
        try {
            $data = $this->getFilteredStats();
            $perusahaan = Perusahaan::first();

            return [
                // Saldo Stat dengan filter info
                Stat::make('Saldo Perusahaan', fn() => 'Rp ' . number_format($perusahaan?->saldo ?? 0, 0, ',', '.'))
                    ->description($this->getFilterDescription())
                    ->descriptionIcon('heroicon-m-calendar')
                    ->color($this->getSaldoColor($perusahaan?->saldo ?? 0)),

                // Transaksi Stats dengan filter
                Stat::make('Transaksi ' . ucfirst($this->activeTab), function () use ($data) {
                    return sprintf(
                        "In: Rp %s | Out: Rp %s",
                        number_format($data['total_in'], 0, ',', '.'),
                        number_format($data['total_out'], 0, ',', '.')
                    );
                })
                    ->description($this->getTransactionDescription($data))
                    ->descriptionIcon('heroicon-m-document-text')
                    ->color('primary'),

                // Stats Metode Pembayaran
                Stat::make('Metode Pembayaran', function () use ($data) {
                    return sprintf(
                        "Tunai: %d | Transfer: %d | Cair Luar: %d",
                        $data['count_tunai'],
                        $data['count_transfer'],
                        $data['count_cair_luar']
                    );
                })
                    ->description($this->getPaymentDescription($data))
                    ->descriptionIcon('heroicon-m-banknotes')
                    ->color('info'),

                // Mutasi Total
                $this->createMutasiStat($data)
            ];
        } catch (\Exception $e) {
            Log::error('Widget Error:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return [
                Stat::make('Error', 'Terjadi kesalahan memuat data')
                    ->description('Silakan refresh halaman')
                    ->descriptionIcon('heroicon-m-exclamation-triangle')
                    ->color('danger')
            ];
        }
    }

    // Determine saldo color based on value
    private function getSaldoColor($saldo): string
    {
        return match (true) {
            $saldo > 1000000000 => 'success',
            $saldo > 100000000 => 'info',
            $saldo > 0 => 'warning',
            default => 'danger'
        };
    }

    // Create DO transaction stat
    private function createDOStat($data): Stat
    {
        return Stat::make('Transaksi DO', function () use ($data) {
            return sprintf(
                "Masuk: Rp %s | Keluar: Rp %s",
                number_format($data['do_in'], 0, ',', '.'),
                number_format($data['do_out'], 0, ',', '.')
            );
        })
            ->description(sprintf(
                "Upah: Rp %s\nBiaya: Rp %s\nHutang: Rp %s",
                number_format($data['upah_bongkar'], 0, ',', '.'),
                number_format($data['biaya_lain'], 0, ',', '.'),
                number_format($data['bayar_hutang'], 0, ',', '.')
            ))
            ->descriptionIcon('heroicon-m-document-text')
            ->color('primary');
    }

    // Create operational transaction stat
    private function createOperasionalStat($data): Stat
    {
        return Stat::make('Transaksi Operasional', function () use ($data) {
            return sprintf(
                "Masuk: Rp %s | Keluar: Rp %s",
                number_format($data['op_in'], 0, ',', '.'),
                number_format($data['op_out'], 0, ',', '.')
            );
        })
            ->description(sprintf(
                "Tunai: Rp %s\nTransfer: Rp %s",
                number_format($data['tunai'], 0, ',', '.'),
                number_format($data['transfer'], 0, ',', '.')
            ))
            ->descriptionIcon('heroicon-m-banknotes')
            ->color($data['op_in'] > $data['op_out'] ? 'success' : 'danger');
    }

    // Create mutation summary stat
    private function createMutasiStat(array $data): Stat
    {
        $selisih = $data['total_in'] - $data['total_out'];

        return Stat::make('Total Mutasi', sprintf("Rp %s", number_format(abs($selisih), 0, ',', '.')))
            ->description(sprintf(
                "Total Masuk: Rp %s\nTotal Keluar: Rp %s",
                number_format($data['total_in'], 0, ',', '.'),
                number_format($data['total_out'], 0, ',', '.')
            ))
            ->descriptionIcon($selisih >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
            ->color($selisih >= 0 ? 'success' : 'danger');
    }

    // Get filtered statistics
    private function getFilteredStats(): array
    {
        $query = LaporanKeuangan::query()
            ->whereBetween('tanggal', [
                $this->startDate->startOfDay(),
                $this->endDate->endOfDay()
            ]);

        // Apply tab filters
        if ($this->activeTab !== 'semua') {
            $query->where('cara_pembayaran', $this->activeTab);
        }

        $stats = $query->select([
            DB::raw('COUNT(*) as total_transaksi'),
            DB::raw('COALESCE(SUM(CASE WHEN jenis_transaksi = "Pemasukan" THEN nominal ELSE 0 END), 0) as total_in'),
            DB::raw('COALESCE(SUM(CASE WHEN jenis_transaksi = "Pengeluaran" THEN nominal ELSE 0 END), 0) as total_out'),
            DB::raw('COUNT(CASE WHEN cara_pembayaran = "Tunai" THEN 1 END) as count_tunai'),
            DB::raw('COUNT(CASE WHEN cara_pembayaran = "Transfer" THEN 1 END) as count_transfer'),
            DB::raw('COUNT(CASE WHEN cara_pembayaran = "cair di luar" THEN 1 END) as count_cair_luar'),
            DB::raw('COALESCE(SUM(CASE WHEN cara_pembayaran = "Tunai" THEN nominal ELSE 0 END), 0) as total_tunai'),
            DB::raw('COALESCE(SUM(CASE WHEN cara_pembayaran = "Transfer" THEN nominal ELSE 0 END), 0) as total_transfer'),
            DB::raw('COALESCE(SUM(CASE WHEN cara_pembayaran = "cair di luar" THEN nominal ELSE 0 END), 0) as total_cair_luar')
        ])->first();

        return [
            'total_transaksi' => (int) $stats->total_transaksi,
            'total_in' => (float) $stats->total_in,
            'total_out' => (float) $stats->total_out,
            'count_tunai' => (int) $stats->count_tunai,
            'count_transfer' => (int) $stats->count_transfer,
            'count_cair_luar' => (int) $stats->count_cair_luar,
            'total_tunai' => (float) $stats->total_tunai,
            'total_transfer' => (float) $stats->total_transfer,
            'total_cair_luar' => (float) $stats->total_cair_luar
        ];
    }
    private function getTransactionDescription(array $data): string
    {
        return sprintf(
            "Total Transaksi: %d\nFilter: %s",
            $data['total_transaksi'],
            ucfirst($this->activeTab)
        );
    }

    private function getPaymentDescription(array $data): string
    {
        return sprintf(
            "Tunai: Rp %s\nTransfer: Rp %s\nCair Luar: Rp %s",
            number_format($data['total_tunai'], 0, ',', '.'),
            number_format($data['total_transfer'], 0, ',', '.'),
            number_format($data['total_cair_luar'], 0, ',', '.')
        );
    }
    private function getFilterDescription(): string
    {
        return sprintf(
            "Filter: %s s/d %s",
            $this->startDate->format('d/m/Y'),
            $this->endDate->format('d/m/Y')
        );
    }

    // Listen for date filter updates
    #[On('filterDate')]
    public function updateDateFilter($startDate, $endDate): void
    {
        $this->startDate = $startDate;
        $this->endDate = $endDate;
    }
}
