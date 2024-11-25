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
    protected static ?string $pollingInterval = '5s';
    protected static bool $isLazy = true;
    protected int | string | array $columnSpan = 'full';

    public $startDate;
    public $endDate;
    public $activeTab = 'semua';

    public function mount(): void
    {
        $this->resetDateRange();
    }

    protected $caraPembayaran = [
        'Tunai' => 'Tunai',
        'Transfer' => 'Transfer',
        'cair di luar' => 'Cair di Luar'
    ];

    private function resetDateRange(): void
    {
        $this->startDate = now()->startOfDay();
        $this->endDate = now()->endOfDay();
    }

    #[On(['refresh-widget', 'transaksi-created', 'transaksi-updated', 'transaksi-deleted', 'saldo-updated'])]
    public function refresh(): void
    {
        Cache::forget('transaksi-stats');
        Cache::forget('perusahaan-data');
        $this->resetDateRange();
    }

    protected function getStats(): array
    {
        try {
            $perusahaan = Perusahaan::first();
            if (!$perusahaan) {
                throw new \Exception('Data perusahaan tidak ditemukan');
            }

            // Query transaksi
            $query = DB::table('transaksi_do')
                ->whereNull('deleted_at')
                ->whereBetween('tanggal', [$this->startDate, $this->endDate]);

            // Basic stats
            $basicStats = $query->select([
                DB::raw('COUNT(*) as total_transaksi'),
                DB::raw('COALESCE(SUM(tonase), 0) as total_tonase'),
                DB::raw('COALESCE(SUM(upah_bongkar), 0) as total_upah_bongkar'),
                DB::raw('COALESCE(SUM(biaya_lain), 0) as total_biaya_lain'),
                DB::raw('COALESCE(SUM(pembayaran_hutang), 0) as total_bayar_hutang'),
            ])->first();

            // Tunai Stats
            $tunaiStats = (clone $query)
                ->where('cara_bayar', 'Tunai')
                ->select([
                    DB::raw('COUNT(*) as tunai_count'),
                    DB::raw('COALESCE(SUM(sisa_bayar), 0) as tunai_amount')
                ])->first();

            // Transfer Stats
            $transferStats = (clone $query)
                ->where('cara_bayar', 'Transfer')
                ->select([
                    DB::raw('COUNT(*) as transfer_count'),
                    DB::raw('COALESCE(SUM(sisa_bayar), 0) as transfer_amount')
                ])->first();

            // Cair Luar Stats
            $cairLuarStats = (clone $query)
                ->where('cara_bayar', 'cair di luar')
                ->select([
                    DB::raw('COUNT(*) as cair_luar_count'),
                    DB::raw('COALESCE(SUM(sisa_bayar), 0) as cair_luar_amount')
                ])->first();

            // Total sisa bayar non-tunai
            $totalSisaBayarNonTunai = $transferStats->transfer_amount + $cairLuarStats->cair_luar_amount;

            // Total saldo termasuk piutang
            $totalSaldo = $perusahaan->saldo;
            $totalPiutang = $totalSisaBayarNonTunai;

            // Total pemasukan dari upah, biaya & hutang
            $totalPemasukanTunai = $basicStats->total_upah_bongkar +
                $basicStats->total_biaya_lain +
                $basicStats->total_bayar_hutang;

            return [
                // Saldo & Piutang
                Stat::make('Total Saldo & Piutang', 'Rp ' . number_format($totalSaldo + $totalPiutang, 0, ',', '.'))
                    ->description(sprintf(
                        "Saldo: Rp %s\nPiutang: Rp %s",
                        number_format($totalSaldo, 0, ',', '.'),
                        number_format($totalPiutang, 0, ',', '.')
                    ))
                    ->color($this->getSaldoColor($totalSaldo)),

                // Pemasukan
                Stat::make('Pemasukan (Upah, Biaya & Hutang)', 'Rp ' . number_format($totalPemasukanTunai, 0, ',', '.'))
                    ->description(sprintf(
                        "Upah: Rp %s\nBiaya: Rp %s\nHutang: Rp %s",
                        number_format($basicStats->total_upah_bongkar, 0, ',', '.'),
                        number_format($basicStats->total_biaya_lain, 0, ',', '.'),
                        number_format($basicStats->total_bayar_hutang, 0, ',', '.')
                    ))
                    ->color('success'),

                // Pembayaran DO
                Stat::make('Pembayaran DO', sprintf(
                    "Tunai: %d DO (Rp %s)",
                    $tunaiStats->tunai_count,
                    number_format($tunaiStats->tunai_amount, 0, ',', '.')
                ))
                    ->description(sprintf(
                        "Transfer: %d DO (Rp %s)\nCair Luar: %d DO (Rp %s)",
                        $transferStats->transfer_count,
                        number_format($transferStats->transfer_amount, 0, ',', '.'),
                        $cairLuarStats->cair_luar_count,
                        number_format($cairLuarStats->cair_luar_amount, 0, ',', '.')
                    ))
                    ->color('warning'),

                // Summary
                Stat::make('Total Transaksi', sprintf(
                    "%d DO | %s Kg",
                    $basicStats->total_transaksi,
                    number_format($basicStats->total_tonase, 0, ',', '.')
                ))
                    ->description(sprintf(
                        "Total Bayar DO: Rp %s",
                        number_format(
                            $tunaiStats->tunai_amount +
                                $transferStats->transfer_amount +
                                $cairLuarStats->cair_luar_amount,
                            0,
                            ',',
                            '.'
                        )
                    ))
                    ->color('primary'),
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

    private function getSaldoColor($saldo): string
    {
        return match (true) {
            $saldo > 50000000 => 'success',
            $saldo > 10000000 => 'info',
            $saldo > 0 => 'warning',
            default => 'danger'
        };
    }
}
