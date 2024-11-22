<?php

namespace App\Filament\Resources\OperasionalResource\Traits;

trait HasOperationalHelpers
{
    private static function updateHutangInfo($state, Forms\Set $set, string $model): void
    {
        if (!$state) return;

        $record = $model::find($state);
        if (!$record) return;

        $hutang = $record->hutang;
        $set('info_hutang', [
            'nominal' => $hutang,
            'formatted' => "Total Hutang: Rp " . number_format($hutang, 0, ',', '.'),
            'color' => $hutang > 0 ? 'danger' : 'success'
        ]);
    }
}
