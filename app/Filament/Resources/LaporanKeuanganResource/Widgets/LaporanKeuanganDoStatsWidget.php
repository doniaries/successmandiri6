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

    // Date filter state
    public $startDate;
    public $endDate;

    // Initialize default dates on mount
    public function mount(): void
    {
        $this->startDate = now()->startOfDay();
        $this->endDate = now()->endOfDay();
    }

    //update stats saldo
    #[On(['refresh-widgets', 'saldo-updated'])]
    public function refresh(): void
    {
        $this->getFilteredStats();
    }

    //  heading
    // protected function getHeading(): ?string
    // {
    //     return 'Ringkasan Laporan Keuangan';
    // }

    // Main stats getter
    protected function getStats(): array
    {
        try {
            $data = $this->getFilteredStats();
            $perusahaan = Perusahaan::first();

            return [
                // Saldo Stats (Tanpa Chart untuk menghindari error)
                Stat::make('Saldo Perusahaan', fn() => 'Rp ' . number_format($perusahaan?->saldo ?? 0, 0, ',', '.'))
                    ->description('Status saldo terkini')
                    ->descriptionIcon('heroicon-m-banknotes')
                    ->color($this->getSaldoColor($perusahaan?->saldo ?? 0)),

                // DO Stats
                $this->createDOStat($data),

                // Operasional Stats
                $this->createOperasionalStat($data),

                // Mutasi/Perubahan Stats
                $this->createMutasiStat($data)
            ];
        } catch (\Exception $e) {
            Log::error('Error getting stats:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return [
                Stat::make('Error', 'Terjadi kesalahan saat memuat data')
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
            $saldo > 1000000000 => 'success', // > 1M
            $saldo > 100000000 => 'info',     // > 100jt
            $saldo > 0 => 'warning',          // > 0
            default => 'danger'               // <= 0
        };
    }

    // Create DO transaction stat
    private function createDOStat($data): Stat
    {
        return Stat::make('Transaksi DO', function () use ($data) {
            return sprintf(
                "In: Rp %s | Out: Rp %s",
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
                "In: Rp %s | Out: Rp %s",
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
    private function createMutasiStat($data): Stat
    {
        $totalIn = $data['do_in'] + $data['op_in'];
        $totalOut = $data['do_out'] + $data['op_out'];
        $selisih = $totalIn - $totalOut;

        return Stat::make('Total Mutasi', sprintf("Rp %s", number_format(abs($selisih), 0, ',', '.')))
            ->description(sprintf(
                "Total In: Rp %s\nTotal Out: Rp %s",
                number_format($totalIn, 0, ',', '.'),
                number_format($totalOut, 0, ',', '.')
            ))
            ->descriptionIcon($selisih >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
            ->color($selisih >= 0 ? 'success' : 'danger');
    }

    // Get filtered statistics
    private function getFilteredStats(): array
    {
        return DB::transaction(function () {
            $query = LaporanKeuangan::query()
                ->when($this->startDate && $this->endDate, function ($q) {
                    $q->whereBetween('tanggal', [$this->startDate, $this->endDate]);
                }, function ($q) {
                    $q->whereDate('tanggal', Carbon::today());
                });

            // Get DO statistics
            $doStats = (clone $query)
                ->where('kategori', 'DO')
                ->select([
                    DB::raw('COALESCE(SUM(CASE WHEN jenis_transaksi = "Pemasukan" THEN nominal ELSE 0 END), 0) as do_in'),
                    DB::raw('COALESCE(SUM(CASE WHEN jenis_transaksi = "Pengeluaran" THEN nominal ELSE 0 END), 0) as do_out'),
                    DB::raw('COALESCE(SUM(CASE WHEN sub_kategori = "Upah Bongkar" THEN nominal ELSE 0 END), 0) as upah_bongkar'),
                    DB::raw('COALESCE(SUM(CASE WHEN sub_kategori = "Biaya Lain" THEN nominal ELSE 0 END), 0) as biaya_lain'),
                    DB::raw('COALESCE(SUM(CASE WHEN sub_kategori = "Bayar Hutang" THEN nominal ELSE 0 END), 0) as bayar_hutang')
                ])
                ->first();

            // Get operational statistics
            $opStats = (clone $query)
                ->where('kategori', 'Operasional')
                ->select([
                    DB::raw('COALESCE(SUM(CASE WHEN jenis_transaksi = "Pemasukan" THEN nominal ELSE 0 END), 0) as op_in'),
                    DB::raw('COALESCE(SUM(CASE WHEN jenis_transaksi = "Pengeluaran" THEN nominal ELSE 0 END), 0) as op_out'),
                    DB::raw('COALESCE(SUM(CASE WHEN cara_pembayaran = "Tunai" THEN nominal ELSE 0 END), 0) as tunai'),
                    DB::raw('COALESCE(SUM(CASE WHEN cara_pembayaran = "Transfer" THEN nominal ELSE 0 END), 0) as transfer')
                ])
                ->first();

            // Map results with proper type casting and null handling
            return [
                'do_in' => (float) $doStats->do_in,
                'do_out' => (float) $doStats->do_out,
                'upah_bongkar' => (float) $doStats->upah_bongkar,
                'biaya_lain' => (float) $doStats->biaya_lain,
                'bayar_hutang' => (float) $doStats->bayar_hutang,
                'op_in' => (float) $opStats->op_in,
                'op_out' => (float) $opStats->op_out,
                'tunai' => (float) $opStats->tunai,
                'transfer' => (float) $opStats->transfer
            ];
        });
    }

    // Listen for date filter updates
    #[On('filterDate')]
    public function updateDateFilter($startDate, $endDate): void
    {
        $this->startDate = $startDate;
        $this->endDate = $endDate;
    }
}
