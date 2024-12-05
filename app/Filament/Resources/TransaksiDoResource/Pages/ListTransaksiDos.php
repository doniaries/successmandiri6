<?php

namespace App\Filament\Resources\TransaksiDoResource\Pages;

use App\Filament\Resources\TransaksiDoResource;
// use App\Filament\Resources\TransaksiDoResource\Widgets\TransaksiDoStatWidget;
use App\Models\Operasional;
use App\Models\TransaksiDo;
use Illuminate\Support\Facades\DB;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListTransaksiDos extends ListRecords
{
    protected static string $resource = TransaksiDoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->icon('heroicon-o-plus')
                ->color('primary')
                ->label('Tambah Transaksi'),  // Ubah ini

        ];
    }

    // Handle filter date changes
    public function updatedTableFilters(): void
    {
        $filter = $this->getTableFilters()['created_at'] ?? null;
        if ($filter) {
            $this->dispatch('filter-transaksi', [
                'startDate' => $filter->getState()['created_from'],
                'endDate' => $filter->getState()['created_to'],
            ]);
        }
    }

    // Handle tab changes
    public function updatedActiveTab(): void
    {
        $this->dispatch('tab-changed', [
            'tab' => $this->activeTab
        ]);
    }

    protected function getHeaderWidgets(): array
    {
        return [
            TransaksiDoResource\Widgets\TransaksiDoStatWidget::class,
        ];
    }

    protected function getFooterWidgets(): array
    {
        return [
            //
        ];
    }

    public function getTabs(): array
    {
        return [
            'hari_ini' => Tab::make('Hari Ini')
            ->icon('heroicon-o-calendar-days')
            ->badge(fn() => $this->getModel()::whereDate('tanggal', now())->count())
            ->modifyQueryUsing(fn(Builder $query) => $query->whereDate('tanggal', now()))
            ->badgeColor('success'),

        'kemarin' => Tab::make('Kemarin')
            ->icon('heroicon-o-calendar')
            ->badge(fn() => $this->getModel()::whereDate('tanggal', now()->subDay())->count())
            ->modifyQueryUsing(fn(Builder $query) => $query->whereDate('tanggal', now()->subDay()))
            ->badgeColor('info'),

        'minggu_ini' => Tab::make('Minggu Ini')
            ->icon('heroicon-o-calendar-days')
            ->badge(fn() => $this->getModel()::whereBetween('tanggal', [now()->startOfWeek(), now()->endOfWeek()])->count())
            ->modifyQueryUsing(fn(Builder $query) => $query->whereBetween('tanggal', [now()->startOfWeek(), now()->endOfWeek()]))
            ->badgeColor('warning'),

            'semua' => Tab::make('Semua Transaksi')
                ->icon('heroicon-o-clipboard-document-list')
                ->badge(fn() => $this->getModel()::count())
                ->modifyQueryUsing(
                    fn(Builder $query) =>
                    $query->whereDate('tanggal', '>=', now()->subDays(30))
                )
                ->badgeColor('primary'),

            'tunai' => Tab::make('Tunai')
                ->modifyQueryUsing(fn(Builder $query) => $query->where('cara_bayar', 'Tunai'))
                ->icon('heroicon-o-banknotes')
                ->badge(fn() => $this->getModel()::where('cara_bayar', 'Tunai')
                    ->when(
                        request('tableFilters.created_at'),
                        fn($query) => $query->whereBetween('tanggal', [
                            request('tableFilters.created_at.created_from'),
                            request('tableFilters.created_at.created_to')
                        ])
                    )->count())
                ->badgeColor('success'),

            'transfer' => Tab::make('Transfer')
                ->modifyQueryUsing(fn(Builder $query) => $query->where('cara_bayar', 'Transfer'))
                ->icon('heroicon-o-credit-card')
                ->badge(fn() => $this->getModel()::where('cara_bayar', 'Transfer')
                    ->when(
                        request('tableFilters.created_at'),
                        fn($query) => $query->whereBetween('tanggal', [
                            request('tableFilters.created_at.created_from'),
                            request('tableFilters.created_at.created_to')
                        ])
                    )->count())
                ->badgeColor('info'),

            'cair_luar' => Tab::make('Cair Di Luar')
                ->modifyQueryUsing(fn(Builder $query) => $query->where('cara_bayar', 'cair di luar'))
                ->icon('heroicon-o-banknotes')
                ->badge(fn() => $this->getModel()::where('cara_bayar', 'cair di luar')
                    ->when(
                        request('tableFilters.created_at'),
                        fn($query) => $query->whereBetween('tanggal', [
                            request('tableFilters.created_at.created_from'),
                            request('tableFilters.created_at.created_to')
                        ])
                    )->count())
                ->badgeColor('warning'),
        ];
    }

    protected function getTabCount(string $tab): int
    {
        $query = $this->getModel()::query();

        // Apply date filter if exists
        $dateFilter = $this->getTableFilters()['created_at'] ?? null;
        if ($dateFilter) {
            $data = $dateFilter->getState();
            if (!empty($data['created_from']) && !empty($data['created_to'])) {
                $query->whereBetween('created_at', [
                    $data['created_from'],
                    $data['created_to']
                ]);
            }
        }

        // Apply tab filter
        if ($tab !== 'semua') {
            $query->where('cara_bayar', $tab);
        }

        return $query->count();
    }

    public function getDefaultActiveTab(): string | int | null
    {
        return 'hari_ini';
    }
}
