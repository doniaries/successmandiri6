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
        'semua' => Tab::make('Semua Transaksi')
            ->icon('heroicon-o-clipboard-document-list')
            ->badge(fn() => TransaksiDo::query()
                ->when($this->getTableFilters()['created_at'] ?? null, function($query, $filter) {
                    $data = $filter->getState();
                    return $query->whereDate('tanggal', '>=', $data['created_from'])
                                ->whereDate('tanggal', '<=', $data['created_to']);
                })->count())
            ->modifyQueryUsing(fn (Builder $query) => $query)
            ->badgeColor('primary'),

        'tunai' => Tab::make('Tunai')
            ->icon('heroicon-o-banknotes')
            ->badge(fn() => TransaksiDo::query()
                ->where('cara_bayar', 'Tunai')
                ->when($this->getTableFilters()['created_at'] ?? null, function($query, $filter) {
                    $data = $filter->getState();
                    return $query->whereDate('tanggal', '>=', $data['created_from'])
                                ->whereDate('tanggal', '<=', $data['created_to']);
                })->count())
            ->modifyQueryUsing(fn (Builder $query) => $query->where('cara_bayar', 'Tunai'))
            ->badgeColor('success'),

            'transfer' => Tab::make('Transfer')
                ->icon('heroicon-o-credit-card')
                ->badge(fn() => TransaksiDo::query()
                    ->where('cara_bayar', 'Transfer')
                    ->when($this->getTableFilters()['created_at'] ?? null, function($query, $filter) {
                        $data = $filter->getState();
                        if (!empty($data['created_from']) && !empty($data['created_to'])) {
                            $query->whereBetween('tanggal', [$data['created_from'], $data['created_to']]);
                        } else {
                            $query->whereDate('tanggal', now());
                        }
                    })->count())
                ->modifyQueryUsing(fn (Builder $query) => $query->where('cara_bayar', 'Transfer'))
                ->badgeColor('info'),

            'cair_luar' => Tab::make('Cair Di Luar')
                ->icon('heroicon-o-banknotes')
                ->badge(fn() => TransaksiDo::query()
                    ->where('cara_bayar', 'cair di luar')
                    ->when($this->getTableFilters()['created_at'] ?? null, function($query, $filter) {
                        $data = $filter->getState();
                        if (!empty($data['created_from']) && !empty($data['created_to'])) {
                            $query->whereBetween('tanggal', [$data['created_from'], $data['created_to']]);
                        } else {
                            $query->whereDate('tanggal', now());
                        }
                    })->count())
                ->modifyQueryUsing(fn (Builder $query) => $query->where('cara_bayar', 'cair di luar'))
                ->badgeColor('warning'),

                'belum_bayar' => Tab::make('Belum Bayar')
                    ->icon('heroicon-o-banknotes')
                    ->badge(fn() => TransaksiDo::query()
                        ->where('cara_bayar', 'Belum Bayar')
                        ->when($this->getTableFilters()['created_at'] ?? null, function($query, $filter) {
                            $data = $filter->getState();
                            if (!empty($data['created_from']) && !empty($data['created_to'])) {
                                $query->whereBetween('tanggal', [$data['created_from'], $data['created_to']]);
                            } else {
                                $query->whereDate('tanggal', now());
                            }
                        })->count())
                    ->modifyQueryUsing(fn (Builder $query) => $query->where('cara_bayar', 'Belum Bayar'))
                    ->badgeColor('danger'),
        ];
    }
}
