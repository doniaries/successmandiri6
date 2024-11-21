<?php

namespace App\Filament\Resources\OperasionalResource\Pages;

use App\Filament\Resources\OperasionalResource;
use App\Models\Operasional;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Pages\ListRecords\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListOperasionals extends ListRecords
{
    protected static string $resource = OperasionalResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    public function getTabs(): array
    {
        return [
            'all' => Tab::make('Semua')
                ->icon('heroicon-o-list-bullet')
                ->badge(fn() => Operasional::count())
                ->modifyQueryUsing(
                    fn(Builder $query) =>
                    $query->whereDate('tanggal', '>=', now()->subDays(30))
                ),

            'pemasukan' => Tab::make('Pemasukan')
                ->modifyQueryUsing(
                    fn(Builder $query) =>
                    $query->where('operasional', 'pemasukan')
                )
                ->icon('heroicon-o-arrow-trending-up')
                ->badge(fn() => Operasional::where('operasional', 'pemasukan')->count())
                ->badgeColor('success'),

            'pengeluaran' => Tab::make('Pengeluaran')
                ->modifyQueryUsing(
                    fn(Builder $query) =>
                    $query->where('operasional', 'pengeluaran')
                )
                ->icon('heroicon-o-arrow-trending-down')
                ->badge(fn() => Operasional::where('operasional', 'pengeluaran')->count())
                ->badgeColor('danger'),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            // OperasionalStatsWidget akan dibuat nanti
        ];
    }
}
