<?php

namespace App\Filament\Resources;

// Model Imports
use Closure;

use Filament\Forms;
use Filament\Tables;
use App\Enums\TipeNama;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use App\Enums\KategoriOperasional;
use Filament\Support\Colors\Color;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Badge;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Forms\Components\FileUpload;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Components\DateTimePicker;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\OperasionalResource\Pages;
use App\Models\{Operasional, Penjual, Pekerja, User, Perusahaan};
use App\Enums\TipePihak;
// use App\Filament\Resources\OperasionalResource\Traits\HasOperationalHelpers;

class OperasionalResource extends Resource
{

    // use HasOperationalHelpers;


    protected static ?string $model = Operasional::class;
    protected static ?string $navigationIcon = 'heroicon-o-banknotes';
    protected static ?string $navigationLabel = 'Operasional';
    protected static ?int $navigationSort = 2;

    // public static function getNavigationBadge(): ?string
    // {
    //     return static::getModel()::count();
    // }


    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Transaksi Operasional')
                    ->description('Masukkan informasi transaksi operasional dengan lengkap')
                    ->icon('heroicon-o-banknotes')
                    ->schema([
                        // Panel Informasi Dasar
                        Forms\Components\Section::make('Informasi Dasar')
                            ->compact()
                            ->schema([
                                Forms\Components\DateTimePicker::make('tanggal')
                                    ->label('Tanggal Transaksi')
                                    ->helperText('Waktu transaksi dilakukan')
                                    ->native(false)
                                    ->timezone('Asia/Jakarta')
                                    ->displayFormat('d/m/Y H:i')
                                    ->default(now())
                                    ->required(),

                                Forms\Components\Select::make('operasional')
                                    ->label('Jenis Transaksi')
                                    ->helperText('Pilih jenis transaksi operasional')
                                    ->options([
                                        'pemasukan' => '💰 Pemasukan',
                                        'pengeluaran' => '💸 Pengeluaran',
                                    ])
                                    ->required()
                                    ->live()
                                    ->afterStateUpdated(fn($state, Forms\Set $set) => $set('kategori', null))
                                    ->native(false),

                                Forms\Components\Select::make('kategori')
                                    ->label('Kategori Transaksi')
                                    ->helperText('Pilih kategori sesuai jenis transaksi')
                                    ->options(function ($get) {
                                        return match ($get('operasional')) {
                                            'pemasukan' => KategoriOperasional::forPemasukan(),
                                            'pengeluaran' => KategoriOperasional::forPengeluaran(),
                                            default => []
                                        };
                                    })
                                    ->required()
                                    ->live()
                                    // ->visible(fn($get) => filled($get('operasional')))
                                    ->native(false),
                            ])
                            ->columns([
                                'sm' => 1,
                                'lg' => 3
                            ]),

                        // Panel Informasi Pihak Terkait
                        Forms\Components\Section::make('Informasi Pihak & Nominal')
                            ->schema([
                                Forms\Components\Grid::make()
                                    ->schema([
                                        Forms\Components\Select::make('tipe_nama')
                                            ->label('Pilih Tipe Pihak')
                                            ->helperText('Pilih jenis pihak terkait transaksi')
                                            ->options([
                                                'penjual' => 'Penjual',
                                                'supir' => 'Supir',
                                                'pekerja' => 'Pekerja',
                                                'user' => 'Karyawan'
                                            ])
                                            ->required()
                                            ->live()
                                            ->afterStateUpdated(function ($state, Forms\Set $set) {
                                                // Reset semua field ID saat tipe berubah
                                                $set('penjual_id', null);
                                                $set('supir_id', null);
                                                $set('pekerja_id', null);
                                                $set('user_id', null);
                                            })
                                            ->native(false)
                                            ->columnSpan(1),

                                        // Dynamic Select Pihak
                                        Forms\Components\Group::make([
                                            Forms\Components\Select::make('penjual_id')
                                                ->label('Pilih Penjual')
                                                ->relationship('penjual', 'nama')
                                                ->searchable()
                                                ->preload()
                                                ->live()
                                                ->createOptionForm([
                                                    Forms\Components\TextInput::make('nama')
                                                        ->required()
                                                        ->unique(ignoreRecord: true)
                                                        ->maxLength(20),
                                                    Forms\Components\TextInput::make('alamat')
                                                        ->maxLength(255),
                                                    Forms\Components\TextInput::make('telepon')
                                                        ->tel(),
                                                    Forms\Components\TextInput::make('hutang')
                                                        ->label('Hutang Awal')
                                                        ->prefix('Rp')
                                                        ->numeric()
                                                        ->default(0)
                                                        ->currencyMask(),
                                                ])
                                                ->visible(fn($get) => $get('tipe_nama') === 'penjual'),

                                            // Select Supir
                                            Forms\Components\Select::make('supir_id')
                                                ->label('Pilih Supir')
                                                ->relationship('supir', 'nama')
                                                ->searchable()
                                                ->preload()
                                                ->live()
                                                ->createOptionForm([
                                                    Forms\Components\TextInput::make('nama')
                                                        ->required()
                                                        ->unique(ignoreRecord: true)
                                                        ->maxLength(20),
                                                    Forms\Components\TextInput::make('alamat')
                                                        ->maxLength(255),
                                                    Forms\Components\TextInput::make('telepon')
                                                        ->tel(),
                                                ])
                                                ->visible(fn($get) => $get('tipe_nama') === 'supir'),

                                            // Select Pekerja
                                            Forms\Components\Select::make('pekerja_id')
                                                ->label('Pilih Pekerja')
                                                ->relationship('pekerja', 'nama')
                                                ->searchable()
                                                ->preload()
                                                ->live()
                                                ->createOptionForm([
                                                    Forms\Components\TextInput::make('nama')
                                                        ->required()
                                                        ->unique(ignoreRecord: true)
                                                        ->maxLength(20),
                                                    Forms\Components\TextInput::make('alamat')
                                                        ->maxLength(255),
                                                    Forms\Components\TextInput::make('telepon')
                                                        ->tel(),
                                                    Forms\Components\TextInput::make('pendapatan')
                                                        ->label('Pendapatan')
                                                        ->prefix('Rp')
                                                        ->numeric()
                                                        ->default(0)
                                                        ->currencyMask(),
                                                ])
                                                ->visible(fn($get) => $get('tipe_nama') === 'pekerja'),

                                            // Select Karyawan
                                            Forms\Components\Select::make('user_id')
                                                ->label('Pilih Karyawan')
                                                ->relationship('user', 'name')
                                                ->searchable()
                                                ->preload()
                                                ->live()
                                                ->createOptionForm([
                                                    Forms\Components\TextInput::make('name')
                                                        ->required()
                                                        ->unique(ignoreRecord: true)
                                                        ->maxLength(50),
                                                    Forms\Components\TextInput::make('email')
                                                        ->email()
                                                        ->required(),
                                                    Forms\Components\TextInput::make('password')
                                                        ->password()
                                                        ->required(),
                                                ])
                                                ->visible(fn($get) => $get('tipe_nama') === 'user'),

                                        ])->columnSpan(1),

                                        // Info Hutang & Nominal
                                        Forms\Components\Group::make([
                                            Forms\Components\Placeholder::make('info_hutang')
                                                ->content(fn($get) => $get('info_hutang'))
                                                ->visible(
                                                    fn($get) =>
                                                    $get('kategori') === 'bayar_hutang' &&
                                                        filled($get('info_hutang'))
                                                ),

                                            Forms\Components\TextInput::make('nominal')
                                                ->label('Nominal Pembayaran')
                                                ->helperText('Masukkan jumlah yang akan dibayarkan')
                                                ->required()
                                                ->numeric()
                                                ->prefix('Rp')
                                                ->live(onBlur: true)
                                                ->afterStateUpdated(function ($state, Forms\Set $set, Forms\Get $get) {
                                                    if ($get('kategori') === 'bayar_hutang') {
                                                        $maxPembayaran = $get('max_pembayaran');
                                                        $nominal = (int)str_replace(['.', ','], '', $state);

                                                        if ($nominal > $maxPembayaran) {
                                                            $set('nominal', number_format($maxPembayaran, 0, ',', '.'));
                                                            Notification::make()
                                                                ->warning()
                                                                ->duration(3000) // Set durasi 3 detik
                                                                ->persistent(false) // Notifikasi akan otomatis hilang
                                                                ->title('⚠️ Pembayaran Disesuaikan')
                                                                ->body('Nominal pembayaran disesuaikan dengan total hutang')
                                                                ->send();
                                                        }
                                                    }
                                                })
                                                ->currencyMask(
                                                    thousandSeparator: '.',
                                                    decimalSeparator: ',',
                                                    precision: 0
                                                ),

                                        ])->columnSpan(1),
                                    ])
                                    ->columns(3),
                            ])
                            ->columns(1)
                    ]),
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
                    // Changed from $record->sub_total to $record->nominal
                    ->formatStateUsing(fn($record) => 'Rp ' . number_format($record->nominal, 0, ',', '.'))
                    ->alignRight()
                    ->sortable()
                    ->summarize([
                        Tables\Columns\Summarizers\Sum::make()
                            ->money('IDR')
                    ]),

                // Tables\Columns\ImageColumn::make('file_bukti')
                //     ->label('Bukti')
                //     ->disk('public')
                //     ->square() // ganti circular() jadi square()
                //     ->width(100),
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

    // public static function getHeaderActions(): array
    // {
    //     return [
    //         \Filament\Actions\Action::make('record_balance')
    //             ->label('Catat Saldo')
    //             ->icon('heroicon-o-currency-dollar')
    //             ->color('success')
    //             ->requiresConfirmation()
    //             ->modalHeading('Catat Saldo Perusahaan')
    //             ->modalDescription('Saldo perusahaan saat ini akan dicatat sebagai catatan operasional.')
    //             ->modalSubmitActionLabel('Ya, Catat Saldo')
    //             ->action(function () {
    //                 try {
    //                     $perusahaan = Perusahaan::first();

    //                     if (!$perusahaan) {
    //                         Notification::make()
    //                             ->title('Error')
    //                             ->body('Tidak ada data perusahaan ditemukan')
    //                             ->danger()
    //                             ->send();
    //                         return;
    //                     }

    //                     // Buat entri operasional untuk saldo
    //                     Operasional::create([
    //                         'tanggal' => now(),
    //                         'nominal' => $perusahaan->saldo,
    //                         'kategori' => KategoriOperasional::TAMBAH_SALDO,
    //                         'operasional' => 'pemasukan',
    //                         'keterangan' => 'Pencatatan saldo perusahaan per tanggal ' . now()->format('d/m/Y H:i'),
    //                         'is_from_transaksi' => false,
    //                     ]);

    //                     Notification::make()
    //                         ->title('Berhasil')
    //                         ->body('Saldo perusahaan sebesar Rp ' . number_format($perusahaan->saldo, 0, ',', '.') . ' berhasil dicatat')
    //                         ->success()
    //                         ->send();
    //                 } catch (\Exception $e) {
    //                     Notification::make()
    //                         ->title('Error')
    //                         ->body('Terjadi kesalahan: ' . $e->getMessage())
    //                         ->danger()
    //                         ->send();
    //                 }
    //             }),
    //         // ... action lainnya
    //     ];
    // }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
