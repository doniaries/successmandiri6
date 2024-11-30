<?php

namespace App\Filament\Resources\PerusahaanResource\Widgets;

use App\Models\Perusahaan;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\On;

class PerusahaanStatsWidget extends BaseWidget
{
    protected static ?string $pollingInterval = '15s';
    protected static bool $isLazy = true;
    protected int | string | array $columnSpan = 'full';

    protected function getStats(): array
    {
        try {
            // Hitung saldo dari tabel laporan_keuangan
            $saldoMasuk = DB::table('laporan_keuangan')
                ->whereNull('deleted_at')
                ->where('jenis_transaksi', 'Pemasukan')
                ->sum('nominal');

            $saldoKeluar = DB::table('laporan_keuangan')
                ->whereNull('deleted_at')
                ->where('jenis_transaksi', 'Pengeluaran')
                ->sum('nominal');

            $sisaSaldo = $saldoMasuk - $saldoKeluar;

            return [
                Stat::make('Saldo Perusahaan', 'Rp ' . number_format($sisaSaldo, 0, ',', '.'))
                    ->description('Saldo terkini perusahaan')
                    ->descriptionIcon('heroicon-m-banknotes')
                    ->color($this->getSaldoColor($sisaSaldo)),
            ];
        } catch (\Exception $e) {
            \Log::error('Error PerusahaanStatsWidget:', [
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
            $saldo > 50000000 => 'success', // > 50jt
            $saldo > 10000000 => 'info',    // > 10jt
            $saldo > 0 => 'warning',        // > 0
            default => 'danger'             // <= 0
        };
    }

    #[On(['refresh-widget', 'saldo-updated'])]
    public function refresh(): void
    {
        $this->getStats();
    }
}
