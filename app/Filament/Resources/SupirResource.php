<?php

namespace App\Filament\Resources;

use App\Models\Supir;
use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\SupirResource\Pages;

class SupirResource extends Resource
{
    protected static ?string $model = Supir::class;
    protected static ?string $navigationIcon = 'heroicon-o-user-group';
    protected static ?string $navigationLabel = 'Data Supir';
    protected static ?string $navigationGroup = 'Master Data';
    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make()
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('nama')
                                    ->required()
                                    ->maxLength(255),

                                TextInput::make('telepon')
                                    ->tel()
                                    ->maxLength(255),

                                TextInput::make('alamat')
                                    ->columnSpan(2)
                                    ->maxLength(255),

                                TextInput::make('hutang')
                                    ->numeric()
                                    ->default(0)
                                    ->disabled()
                                    ->dehydrated()
                                    ->mask(
                                        fn(TextInput\Mask $mask) => $mask
                                            ->numeric()
                                            ->thousandsSeparator('.')
                                            ->decimalSeparator(',')
                                    ),
                            ]),
                    ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('nama')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('telepon')
                    ->searchable(),

                TextColumn::make('alamat')
                    ->searchable()
                    ->wrap(),

                TextColumn::make('formatted_hutang')
                    ->label('Hutang')
                    ->sortable('hutang'),

                TextColumn::make('kendaraan_count')
                    ->label('Total Kendaraan')
                    ->counts('kendaraan'),

                TextColumn::make('transaksiDo_count')
                    ->label('Total DO')
                    ->counts('transaksiDo'),
            ])
            ->defaultSort('nama', 'asc')
            ->filters([
                Tables\Filters\TrashedFilter::make(),
                Tables\Filters\Filter::make('has_hutang')
                    ->query(fn(Builder $query): Builder => $query->where('hutang', '>', 0))
                    ->label('Ada Hutang')
                    ->toggle()
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSupirs::route('/'),
            'create' => Pages\CreateSupir::route('/create'),
            'edit' => Pages\EditSupir::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withCount(['kendaraan', 'transaksiDo'])
            ->withTrashed();
    }
}