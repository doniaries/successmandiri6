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



    protected function getHeaderWidgets(): array
    {
        return [
            // TransaksiDoStatWidget::make(),
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
                ->badge(fn() => $this->getModel()::count())
                ->modifyQueryUsing(
                    fn(Builder $query) =>
                    $query->whereDate('tanggal', '>=', now()->subDays(30))
                )
                ->badgeColor('primary'),

            'tunai' => Tab::make('Tunai')
                ->modifyQueryUsing(fn(Builder $query) => $query->where('cara_bayar', 'Tunai'))
                ->icon('heroicon-o-banknotes')
                ->badge(fn() => $this->getModel()::where('cara_bayar', 'Tunai')->count())
                ->badgeColor('success'),

            'transfer' => Tab::make('Transfer')
                ->modifyQueryUsing(fn(Builder $query) => $query->where('cara_bayar', 'Transfer'))
                ->icon('heroicon-o-credit-card')
                ->badge(fn() => $this->getModel()::where('cara_bayar', 'Transfer')->count())
                ->badgeColor('info'),

            'cair di luar' => Tab::make('Cair di Luar') // Kapitalisasi nama untuk konsistensi
                ->modifyQueryUsing(fn(Builder $query) => $query->where('cara_bayar', 'cair di luar')) // Ubah ke cara_bayar
                ->icon('heroicon-o-check-circle')
                ->badge(fn() => $this->getModel()::where('cara_bayar', 'cair di luar')->count()) // Ubah ke cara_bayar
                ->badgeColor('danger'),
        ];
    }
}
