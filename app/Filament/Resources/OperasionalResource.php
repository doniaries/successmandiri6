<?php

namespace App\Filament\Resources;

use App\Enums\KategoriOperasional; // Import enum yang sudah dibuat
use App\Models\{Operasional, Penjual, Pekerja, User};
use Filament\Forms;
use App\Filament\Resources\OperasionalResource\Pages;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Support\Colors\Color;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class OperasionalResource extends Resource
{
    protected static ?string $model = Operasional::class;
    protected static ?string $navigationIcon = 'heroicon-o-banknotes';
    protected static ?string $navigationLabel = 'Operasional';
    protected static ?int $navigationSort = 5;

    // public static function getNavigationBadge(): ?string
    // {
    //     return static::getModel()::count();
    // }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Transaksi')
                    ->description('Input informasi dasar transaksi')
                    ->schema([
                        Forms\Components\Grid::make()
                            ->schema([
                                Forms\Components\DateTimePicker::make('tanggal')
                                    ->label('Tanggal')
                                    ->timezone('Asia/Jakarta')
                                    ->displayFormat('d/m/Y H:i')
                                    ->default(now())
                                    ->required(),

                                // Operasional akan otomatis terisi dari kategori
                                Forms\Components\Select::make('operasional')
                                    ->label('Jenis Operasional')
                                    ->options(Operasional::JENIS_OPERASIONAL)
                                    ->required()
                                    ->live()
                                    ->afterStateUpdated(
                                        fn($state, Forms\Set $set) =>
                                        $set('kategori', null)
                                    ),

                                // Kategori berdasarkan jenis operasional
                                Forms\Components\Select::make('kategori')
                                    ->label('Kategori')
                                    ->options(function ($get) {
                                        $jenis = $get('operasional');
                                        return match ($jenis) {
                                            'pemasukan' => KategoriOperasional::forPemasukan(),
                                            'pengeluaran' => KategoriOperasional::forPengeluaran(),
                                            default => []
                                        };
                                    })
                                    ->required()
                                    ->live()
                                    ->visible(fn($get) => filled($get('operasional')))
                            ])->columns(3),

                        Forms\Components\Grid::make()
                            ->schema([
                                Forms\Components\Select::make('tipe_nama')
                                    ->label('Tipe Pihak')
                                    ->options([
                                        'penjual' => 'Penjual',
                                        'user' => 'Karyawan',
                                        'pekerja' => 'Pekerja'
                                    ])
                                    ->required()
                                    ->live()
                                    ->afterStateUpdated(fn($state, Forms\Set $set) => [
                                        $set('penjual_id', null),
                                        $set('user_id', null),
                                        $set('pekerja_id', null)
                                    ]),

                                // Dynamic relation based on tipe_nama
                                Forms\Components\Select::make('penjual_id')
                                    ->label('Nama Penjual')
                                    ->relationship('penjual', 'nama')
                                    ->searchable()
                                    ->preload()
                                    ->visible(fn($get) => $get('tipe_nama') === 'penjual'),

                                Forms\Components\Select::make('user_id')
                                    ->label('Nama Karyawan')
                                    ->relationship('user', 'name')
                                    ->searchable()
                                    ->preload()
                                    ->visible(fn($get) => $get('tipe_nama') === 'user'),

                                Forms\Components\Select::make('pekerja_id')
                                    ->label('Nama Pekerja')
                                    ->relationship('pekerja', 'nama')
                                    ->createOptionForm([
                                        Forms\Components\TextInput::make('nama')
                                            ->label('Nama Pekerja')
                                            ->minLength(3)
                                            ->required(),
                                        Forms\Components\TextInput::make('alamat')
                                            ->label('Alamat')
                                            ->required(),
                                        Forms\Components\TextInput::make('telepon')
                                            ->label('Nomor Kontak')
                                            ->tel()
                                            ->required(),

                                    ])
                                    ->searchable()
                                    ->live()
                                    ->preload()
                                    ->visible(fn($get) => $get('tipe_nama') === 'pekerja'),

                                Forms\Components\TextInput::make('nominal')
                                    ->label('Nominal')
                                    ->required()
                                    ->numeric()
                                    ->prefix('Rp')
                                    ->live(onBlur: true)
                                    ->currencyMask(
                                        thousandSeparator: '.',
                                        decimalSeparator: ',',
                                        precision: 0
                                    ),
                            ])->columns(2),

                        Forms\Components\Grid::make()
                            ->schema([
                                Forms\Components\Textarea::make('keterangan')
                                    ->label('Keterangan')
                                    ->rows(3),

                                Forms\Components\FileUpload::make('file_bukti')
                                    ->label('Upload Bukti')
                                    ->image()
                                    ->disk('public')  // Tambahkan ini
                                    ->directory('bukti-operasional')
                                    ->preserveFilenames()
                                    ->imageEditor() // Tambahkan ini
                                    ->imageEditorMode(2) // Tambahkan ini
                                    ->openable() // Tambahkan ini
                                    ->downloadable() // Tambahkan ini
                                    ->columnSpanFull()
                            ])->columns(2)
                    ])
                    ->columnSpanFull()
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('tanggal')
                    ->label('Tanggal')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),

                Tables\Columns\TextColumn::make('operasional')
                    ->label('Jenis')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'pemasukan' => 'success',
                        'pengeluaran' => 'danger',
                        default => 'gray'
                    }),

                Tables\Columns\TextColumn::make('kategori')
                    ->label('Kategori')
                    ->formatStateUsing(fn($record) => $record->kategoriLabel)
                    ->searchable(),

                Tables\Columns\TextColumn::make('nama')
                    ->label('Nama')
                    ->description(fn($record) => ucfirst($record->tipe_nama))
                    ->searchable(),

                Tables\Columns\TextColumn::make('nominal')
                    ->label('Nominal')
                    ->money('IDR')
                    ->alignRight()
                    ->sortable()
                    ->summarize([
                        Tables\Columns\Summarizers\Sum::make()
                            ->money('IDR')
                    ]),

                Tables\Columns\ImageColumn::make('file_bukti')
                    ->label('Bukti')
                    ->disk('public')
                    ->square() // ganti circular() jadi square()
                    ->width(100),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('operasional')
                    ->options(Operasional::JENIS_OPERASIONAL),
                Tables\Filters\TrashedFilter::make()
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\ForceDeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
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
            'index' => Pages\ListOperasionals::route('/'),
            'create' => Pages\CreateOperasional::route('/create'),
            'edit' => Pages\EditOperasional::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
