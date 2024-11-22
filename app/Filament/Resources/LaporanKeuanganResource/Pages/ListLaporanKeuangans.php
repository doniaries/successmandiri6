<?php

namespace App\Filament\Resources\LaporanKeuanganResource\Pages;

use App\Filament\Resources\LaporanKeuanganResource;
// use App\Filament\Resources\LaporanKeuanganResource\Widgets\LaporanKeuanganDoStatsWidget;
use App\Filament\Resources\TransaksiDoResource\Widgets\TransaksiDoStatWidget;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

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
            // TransaksiDoStatWidget::make(),
        ];
    }
}
