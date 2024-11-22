<?php

namespace App\Filament\Resources\TransaksiDoResource\Widgets;

use App\Models\{TransaksiDo, Perusahaan};
use Filament\Widgets\StatsOverviewWidget\Stat;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Carbon\Carbon;
use Illuminate\Support\Facades\{DB, Log};
use Livewire\Attributes\On;

class TransaksiDoStatWidget extends BaseWidget
{
    protected static ?int $sort = 1;
    protected static ?string $pollingInterval = '15s';
    protected static bool $isLazy = true;
    protected int | string | array $columnSpan = 'full';

    // State untuk filter
    public $startDate;
    public $endDate;
    public $activeTab = 'semua';

    public function mount(): void
    {
        $this->startDate = now()->subDays(30)->startOfDay();
        $this->endDate = now()->endOfDay();
    }

    public function getHeading(): ?string
    {
        return match ($this->activeTab) {
            'semua' => 'Ringkasan Transaksi',
            'tunai' => 'Ringkasan Tunai',
            'transfer' => 'Ringkasan Transfer',
            'cair di luar' => 'Ringkasan Cair di Luar',
            default => 'Ringkasan Transaksi'
        };
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
    }

    public function getStats(): array
    {
        try {
            // Get data perusahaan
            $perusahaan = Perusahaan::first();
            $saldoPerusahaan = $perusahaan ? $perusahaan->saldo : 0;

            // Tentukan warna berdasarkan saldo
            $colorSaldo = match (true) {
                $saldoPerusahaan > 10000000 => 'success',
                $saldoPerusahaan > 5000000 => 'warning',
                default => 'danger'
            };

            // Get statistik transaksi
            $stats = DB::table('transaksi_do')
                ->whereBetween('tanggal', [
                    $this->startDate,
                    $this->endDate
                ])
                ->when($this->activeTab !== 'semua', function ($q) {
                    return $q->where('cara_bayar', $this->activeTab);
                })
                ->select([
                    // Perhitungan aggregate
                    DB::raw('COUNT(*) as total_transaksi'),
                    DB::raw('COALESCE(SUM(tonase), 0) as total_tonase'),
                    DB::raw('COALESCE(SUM(total), 0) as total_nilai'),
                    DB::raw('COALESCE(SUM(upah_bongkar), 0) as total_upah'),
                    DB::raw('COALESCE(SUM(biaya_lain), 0) as total_biaya'),
                    DB::raw('COALESCE(SUM(pembayaran_hutang), 0) as total_hutang'),
                    DB::raw('COALESCE(SUM(sisa_bayar), 0) as total_bayar'),

                    // Count berdasarkan cara bayar
                    DB::raw('COUNT(CASE WHEN cara_bayar = "Tunai" THEN 1 END) as count_tunai'),
                    DB::raw('COUNT(CASE WHEN cara_bayar = "Transfer" THEN 1 END) as count_transfer'),
                    DB::raw('COUNT(CASE WHEN cara_bayar = "cair di luar" THEN 1 END) as count_cair'),

                    // Total berdasarkan cara bayar
                    DB::raw('COALESCE(SUM(CASE WHEN cara_bayar = "Tunai" THEN sisa_bayar ELSE 0 END), 0) as bayar_tunai'),
                    DB::raw('COALESCE(SUM(CASE WHEN cara_bayar = "Transfer" THEN sisa_bayar ELSE 0 END), 0) as bayar_transfer'),
                    DB::raw('COALESCE(SUM(CASE WHEN cara_bayar = "cair di luar" THEN sisa_bayar ELSE 0 END), 0) as bayar_cair')
                ])
                ->first();

            // Convert to array & default 0
            $data = array_map(function ($value) {
                return $value ?? 0;
            }, (array)$stats);

            return [
                // Stat 1: Saldo Perusahaan & Info Transaksi
                Stat::make('Saldo Perusahaan', 'Rp ' . number_format($saldoPerusahaan, 0, ',', '.'))
                    ->description(sprintf(
                        "%d Transaksi | %s Kg\nTunai: %d | Transfer: %d | Cair: %d",
                        $data['total_transaksi'],
                        number_format($data['total_tonase'], 0, ',', '.'),
                        $data['count_tunai'],
                        $data['count_transfer'],
                        $data['count_cair']
                    ))
                    ->descriptionIcon('heroicon-o-building-library')
                    ->chart([7, 4, 6, 5, 7, 6, 5])
                    ->color($colorSaldo), // Gunakan string langsung, bukan closure

                // Stat 2: Nilai & Detail Transaksi
                Stat::make('Total Nilai', sprintf('Rp %s', number_format($data['total_nilai'], 0, ',', '.')))
                    ->description(sprintf(
                        "Upah: Rp %s\nBiaya: Rp %s\nHutang: Rp %s",
                        number_format($data['total_upah'], 0, ',', '.'),
                        number_format($data['total_biaya'], 0, ',', '.'),
                        number_format($data['total_hutang'], 0, ',', '.')
                    ))
                    ->descriptionIcon('heroicon-m-banknotes')
                    ->color('warning'),

                // Stat 3: Detail Pembayaran
                Stat::make('Total Pembayaran', sprintf('Rp %s', number_format($data['total_bayar'], 0, ',', '.')))
                    ->description(sprintf(
                        "Tunai: Rp %s\nTransfer: Rp %s\nCair: Rp %s",
                        number_format($data['bayar_tunai'], 0, ',', '.'),
                        number_format($data['bayar_transfer'], 0, ',', '.'),
                        number_format($data['bayar_cair'], 0, ',', '.')
                    ))
                    ->descriptionIcon('heroicon-m-credit-card')
                    ->color('success')
                    ->chart([3, 5, 4, 6, 3, 5, 4])
            ];
        } catch (\Exception $e) {
            Log::error('Error TransaksiDoStatWidget:', [
                'message' => $e->getMessage(),
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

    // Refresh event handler
    #[On(['refresh-widget'])]
    public function refresh(): void
    {
        $this->getStats();
    }
}