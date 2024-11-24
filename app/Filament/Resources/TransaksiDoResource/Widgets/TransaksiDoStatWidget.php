<?php

namespace App\Filament\Resources\TransaksiDoResource\Widgets;

use App\Models\{TransaksiDo, Perusahaan};
use Filament\Widgets\StatsOverviewWidget\Stat;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Carbon\Carbon;
use Illuminate\Support\Facades\{DB, Log, Cache};
use Livewire\Attributes\On;

class TransaksiDoStatWidget extends BaseWidget
{
    protected static ?int $sort = 1;
    protected static ?string $pollingInterval = '5s'; // Persingkat polling
    protected static bool $isLazy = true;
    protected int | string | array $columnSpan = 'full';

    // State untuk filter
    public $startDate;
    public $endDate;
    public $activeTab = 'semua';

    public function mount(): void
    {
        // Default tanggal hari ini
        $this->resetDateRange();
    }

    private function resetDateRange(): void
    {
        $this->startDate = now()->startOfDay();
        $this->endDate = now()->endOfDay();
    }

    public function getHeading(): ?string
    {
        return match ($this->activeTab) {
            'semua' => 'Ringkasan Transaksi Hari Ini',
            'tunai' => 'Ringkasan Tunai Hari Ini',
            'transfer' => 'Ringkasan Transfer Hari Ini',
            'cair di luar' => 'Ringkasan Cair di Luar Hari Ini',
            default => 'Ringkasan Transaksi Hari Ini'
        };
    }

    // Handler untuk semua event update
    #[On(['refresh-widget', 'transaksi-created', 'transaksi-updated', 'transaksi-deleted', 'saldo-updated'])]
    public function refresh(): void
    {
        // Clear cache
        Cache::forget('transaksi-stats');
        Cache::forget('perusahaan-data');

        // Reset date range ke hari ini
        $this->resetDateRange();

        // Force refresh data
        $this->getStats();

        // Log untuk debugging
        Log::info('TransaksiDoStatWidget refreshed', [
            'time' => now()->format('H:i:s'),
            'date_range' => [
                'start' => $this->startDate->format('Y-m-d H:i:s'),
                'end' => $this->endDate->format('Y-m-d H:i:s')
            ],
            'trigger' => debug_backtrace()[1]['function'] ?? 'unknown'
        ]);
    }

    // Handler filter & tab
    #[On(['filter-transaksi', 'tab-changed'])]
    public function handleFilter($data = []): void
    {
        if (isset($data['startDate'])) {
            $this->startDate = Carbon::parse($data['startDate'])->startOfDay();
        }

        if (isset($data['endDate'])) {
            $this->endDate = Carbon::parse($data['endDate'])->endOfDay();
        }

        if (isset($data['tab'])) {
            $this->activeTab = $data['tab'];
        }

        // Clear cache when filter changes
        Cache::forget('transaksi-stats');
    }

    public function getStats(): array
    {
        try {
            // Get fresh data perusahaan
            $perusahaan = Cache::remember('perusahaan-data', 5, function () {
                return Perusahaan::first();
            });

            $saldoPerusahaan = $perusahaan ? $perusahaan->saldo : 0;

            // Get statistik transaksi
            $stats = Cache::remember('transaksi-stats', 5, function () {
                return DB::table('transaksi_do')
                    ->whereNull('deleted_at')
                    ->whereBetween('tanggal', [
                        $this->startDate,
                        $this->endDate
                    ])
                    ->when($this->activeTab !== 'semua', function ($q) {
                        return $q->where('cara_bayar', $this->activeTab);
                    })
                    ->select([
                        // Aggregate counts
                        DB::raw('COUNT(*) as total_transaksi'),
                        DB::raw('SUM(tonase) as total_tonase'),

                        // Total nilai & komponen
                        DB::raw('COALESCE(SUM(total), 0) as total_nilai'),
                        DB::raw('COALESCE(SUM(upah_bongkar), 0) as total_upah'),
                        DB::raw('COALESCE(SUM(biaya_lain), 0) as total_biaya'),
                        DB::raw('COALESCE(SUM(pembayaran_hutang), 0) as total_hutang'),
                        DB::raw('COALESCE(SUM(sisa_bayar), 0) as total_bayar'),

                        // Count per cara bayar
                        DB::raw('COUNT(CASE WHEN cara_bayar = "Tunai" THEN 1 END) as count_tunai'),
                        DB::raw('COUNT(CASE WHEN cara_bayar = "Transfer" THEN 1 END) as count_transfer'),
                        DB::raw('COUNT(CASE WHEN cara_bayar = "cair di luar" THEN 1 END) as count_cair'),

                        // Total pembayaran per cara bayar
                        DB::raw('COALESCE(SUM(CASE WHEN cara_bayar = "Tunai" THEN sisa_bayar ELSE 0 END), 0) as bayar_tunai'),
                        DB::raw('COALESCE(SUM(CASE WHEN cara_bayar = "Transfer" THEN sisa_bayar ELSE 0 END), 0) as bayar_transfer'),
                        DB::raw('COALESCE(SUM(CASE WHEN cara_bayar = "cair di luar" THEN sisa_bayar ELSE 0 END), 0) as bayar_cair')
                    ])
                    ->first();
            });

            // Convert to array & ensure numeric
            $data = array_map(function ($value) {
                return is_numeric($value) ? $value : 0;
            }, (array)$stats);

            // Format Stats
            return [
                // Stat 1: Saldo & Info Transaksi
                Stat::make('Saldo Perusahaan', 'Rp ' . number_format($saldoPerusahaan, 0, ',', '.'))
                    ->description(sprintf(
                        "%d Transaksi | %s Kg\nTunai: %d | Transfer: %d | Cair: %d",
                        $data['total_transaksi'],
                        number_format($data['total_tonase'] ?? 0, 0, ',', '.'),
                        $data['count_tunai'],
                        $data['count_transfer'],
                        $data['count_cair']
                    ))
                    ->descriptionIcon('heroicon-o-building-library')
                    ->color(match (true) {
                        $saldoPerusahaan > 10000000 => 'success',
                        $saldoPerusahaan > 5000000 => 'warning',
                        default => 'danger'
                    }),

                // Stat 2: Total Nilai & Komponennya
                Stat::make('Total Nilai', 'Rp ' . number_format($data['total_nilai'], 0, ',', '.'))
                    ->description(sprintf(
                        "Upah: Rp %s\nBiaya: Rp %s\nHutang: Rp %s",
                        number_format($data['total_upah'], 0, ',', '.'),
                        number_format($data['total_biaya'], 0, ',', '.'),
                        number_format($data['total_hutang'], 0, ',', '.')
                    ))
                    ->descriptionIcon('heroicon-m-banknotes')
                    ->color('warning'),

                // Stat 3: Total & Detail Pembayaran
                Stat::make('Total Pembayaran', 'Rp ' . number_format($data['total_bayar'], 0, ',', '.'))
                    ->description(sprintf(
                        "Tunai: Rp %s\nTransfer: Rp %s\nCair: Rp %s",
                        number_format($data['bayar_tunai'], 0, ',', '.'),
                        number_format($data['bayar_transfer'], 0, ',', '.'),
                        number_format($data['bayar_cair'], 0, ',', '.')
                    ))
                    ->descriptionIcon('heroicon-m-credit-card')
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
                    ->descriptionIcon('heroicon-m-exclamation-triangle')
                    ->color('danger')
            ];
        }
    }
}