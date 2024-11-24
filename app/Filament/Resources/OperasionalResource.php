<?php

namespace App\Filament\Resources;

// Model Imports
use App\Models\{Operasional, Penjual, Pekerja, User};

// Filament Core Imports
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

// Filament UI Components
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Badge;
use Filament\Forms\Components\Group;

// Support & Database
use Filament\Support\Colors\Color;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Closure;

// Local Imports
use App\Enums\KategoriOperasional;
use App\Filament\Resources\OperasionalResource\Pages;
// use App\Filament\Resources\OperasionalResource\Traits\HasOperationalHelpers;

class OperasionalResource extends Resource
{

    // use HasOperationalHelpers;


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
                Forms\Components\Section::make()
                    ->schema([
                        // Grid untuk informasi dasar
                        Forms\Components\Grid::make()
                            ->schema([
                                Forms\Components\DateTimePicker::make('tanggal')
                                    ->label('Tanggal')
                                    ->native(false)
                                    ->timezone('Asia/Jakarta')
                                    ->displayFormat('d/m/Y H:i')
                                    ->default(now())
                                    ->required(),

                                Forms\Components\Select::make('operasional')
                                    ->label('Jenis')
                                    ->options(Operasional::JENIS_OPERASIONAL)
                                    ->required()
                                    ->live()
                                    ->afterStateUpdated(fn($state, Forms\Set $set) => $set('kategori', null)),

                                Forms\Components\Select::make('kategori')
                                    ->label('Kategori')
                                    ->options(function ($get) {
                                        return match ($get('operasional')) {
                                            'pemasukan' => KategoriOperasional::forPemasukan(),
                                            'pengeluaran' => KategoriOperasional::forPengeluaran(),
                                            default => []
                                        };
                                    })
                                    ->required()
                                    ->live()
                                    ->visible(fn($get) => filled($get('operasional')))
                            ])->columns(3),

                        // Grid untuk pemilihan pihak dan nominal
                        Forms\Components\Grid::make()
                            ->schema([
                                Forms\Components\Select::make('tipe_nama')
                                    ->label('Pihak')
                                    ->options([
                                        'penjual' => 'Penjual',
                                        'pekerja' => 'Pekerja',
                                        'user' => 'Karyawan'
                                    ])
                                    ->required()
                                    ->live()
                                    ->visible(fn($get) => filled($get('kategori')))
                                    ->afterStateUpdated(fn($state, Forms\Set $set) => [
                                        $set('penjual_id', null),
                                        $set('pekerja_id', null),
                                        $set('user_id', null)
                                    ]),

                                // Dynamic select untuk pihak terkait
                                Forms\Components\Group::make()
                                    ->schema([
                                        // Select Penjual
                                        Forms\Components\Select::make('penjual_id')
                                            ->label('Pilih Penjual')
                                            ->relationship('penjual', 'nama')
                                            ->searchable()
                                            ->preload()
                                            ->live()
                                            ->afterStateUpdated(function ($state, Forms\Set $set) {
                                                if ($state) {
                                                    $penjual = Penjual::find($state);
                                                    $set('info_hutang', "Sisa Hutang: Rp " . number_format($penjual?->hutang ?? 0, 0, ',', '.'));
                                                }
                                            })
                                            ->visible(fn($get) => $get('tipe_nama') === 'penjual'),

                                        // Select Pekerja
                                        Forms\Components\Select::make('pekerja_id')
                                            ->label('Pilih Pekerja')
                                            ->relationship('pekerja', 'nama')
                                            ->searchable()
                                            ->preload()
                                            ->live()
                                            ->afterStateUpdated(function ($state, Forms\Set $set) {
                                                if ($state) {
                                                    $pekerja = Pekerja::find($state);
                                                    $set('info_hutang', "Sisa Hutang: Rp " . number_format($pekerja?->hutang ?? 0, 0, ',', '.'));
                                                }
                                            })
                                            ->visible(fn($get) => $get('tipe_nama') === 'pekerja'),

                                        // Select User/Karyawan
                                        Forms\Components\Select::make('user_id')
                                            ->label('Pilih Karyawan')
                                            ->relationship('user', 'name')
                                            ->searchable()
                                            ->preload()
                                            ->visible(fn($get) => $get('tipe_nama') === 'user'),

                                        // Info Hutang (hanya muncul untuk pinjaman)
                                        Forms\Components\Placeholder::make('info_hutang')
                                            ->content(fn($get) => $get('info_hutang'))
                                            ->visible(
                                                fn($get) =>
                                                $get('operasional') === 'pengeluaran' &&
                                                    $get('kategori') === 'pinjaman' &&
                                                    filled($get('info_hutang'))
                                            )
                                    ]),

                                Forms\Components\TextInput::make('nominal')
                                    ->label('Nominal')
                                    ->required()
                                    ->numeric()
                                    ->visible(fn($get) => $get('tipe_nama') === 'tipe_nama')
                                    ->prefix('Rp')
                                    ->live(onBlur: true)
                                    ->currencyMask(
                                        thousandSeparator: '.',
                                        decimalSeparator: ',',
                                        precision: 0
                                    ),
                            ])->columns(2),

                        // Keterangan dan Bukti
                        Forms\Components\Grid::make()
                            ->schema([
                                Forms\Components\Textarea::make('keterangan')
                                    ->label('Keterangan')
                                    ->visible(fn($get) => $get('nominal') === 'nominal')
                                    ->rows(1),
                                Forms\Components\FileUpload::make('file_bukti')
                                    ->label('Upload Bukti')
                                    ->visible(fn($get) => $get('keterangan') === 'keterangan')
                                    ->image()
                                    ->disk('public')
                                    ->directory('bukti-operasional')
                                    ->imageEditor()
                                    ->openable()
                            ])->columns(2)
                    ])->columns(1)
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
