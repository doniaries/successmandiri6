<?php

namespace App\Filament\Resources\LaporanKeuanganResource\Pages;

use Filament\Actions;
// use App\Filament\Resources\LaporanKeuanganResource\Widgets\LaporanKeuanganDoStatsWidget;
// use App\Filament\Resources\TransaksiDoResource\Widgets\TransaksiDoStatWidget;
use Livewire\Attributes\On;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;
use Filament\Resources\Pages\ListRecords\Tab;
use App\Filament\Resources\LaporanKeuanganResource;
use App\Filament\Resources\TransaksiDoResource\Widgets\TransaksiDoStatWidget;

class ListLaporanKeuangans extends ListRecords
{
    protected static string $resource = LaporanKeuanganResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Actions\CreateAction::make(),//disable tombol buat
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            // LaporanKeuanganDoStatsWidget::class,
            // LaporanKeuanganResource\Widgets\LaporanKeuanganDoStatsWidget::class,
            TransaksiDoStatWidget::make(),
        ];
    }

    // Handle filter date dan update widget
    public function updatedTableFilters(): void
    {
        $filter = $this->getTableFilters()['date_range'] ?? null;
        if ($filter) {
            $this->dispatch('filter-laporan', [
                'startDate' => $filter->getState()['dari_tanggal'],
                'endDate' => $filter->getState()['sampai_tanggal'],
            ]);
        }
    }
    // Handle perubahan tab
    public function updatedActiveTab(): void
    {
        $this->dispatch('tab-changed', [
            'tab' => $this->activeTab
        ]);
    }

    public function getTabs(): array
    {
        return [
            'semua' => Tab::make('Semua Laporan')
                ->icon('heroicon-o-clipboard-document-list')
                ->badge($this->getTabCount('semua'))
                ->modifyQueryUsing(fn(Builder $query) => $query)
                ->badgeColor('primary'),

            'tunai' => Tab::make('Tunai')
                ->icon('heroicon-o-banknotes')
                ->badge($this->getTabCount('Tunai'))
                ->modifyQueryUsing(fn(Builder $query) => $query->where('cara_pembayaran', 'Tunai'))
                ->badgeColor('success'),

            'transfer' => Tab::make('Transfer')
                ->icon('heroicon-o-credit-card')
                ->badge($this->getTabCount('Transfer'))
                ->modifyQueryUsing(fn(Builder $query) => $query->where('cara_pembayaran', 'Transfer'))
                ->badgeColor('info'),

            'cair_di_luar' => Tab::make('Cair di Luar')
                ->icon('heroicon-o-check-circle')
                ->badge($this->getTabCount('cair di luar'))
                ->modifyQueryUsing(fn(Builder $query) => $query->where('cara_pembayaran', 'cair di luar'))
                ->badgeColor('warning'),
        ];
    }

    protected function getTabCount(string $tab): int
    {
        $query = $this->getModel()::query();

        // Apply date filter if exists
        $dateFilter = $this->getTableFilters()['date_range'] ?? null;
        if ($dateFilter) {
            $data = $dateFilter->getState();
            if (!empty($data['dari_tanggal']) && !empty($data['sampai_tanggal'])) {
                $query->whereBetween('tanggal', [
                    $data['dari_tanggal'],
                    $data['sampai_tanggal']
                ]);
            }
        }

        // Apply tab filter
        if ($tab !== 'semua') {
            $query->where('cara_pembayaran', $tab);
        }

        return $query->count();
    }
}
