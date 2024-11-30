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
use App\Models\{Operasional, Penjual, Pekerja, User};
use App\Enums\TipePihak;
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
                                    ->visible(fn($get) => filled($get('operasional')))
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
                                            ->options(collect(TipeNama::cases())->mapWithKeys(fn($tipe) => [
                                                $tipe->value => $tipe->getLabel()
                                            ]))
                                            ->required()
                                            ->live()
                                            ->enum(TipeNama::class)
                                            ->visible(fn($get) => filled($get('kategori')))
                                            ->afterStateUpdated(fn($state, Forms\Set $set) => [
                                                $set('penjual_id', null),
                                                $set('pekerja_id', null),
                                                $set('user_id', null)
                                            ])
                                            ->native(false)
                                            ->columnSpan(1),

                                        // Dynamic Select Pihak
                                        Forms\Components\Group::make([
                                            Forms\Components\Select::make('penjual_id')
                                                ->label('Pilih Penjual')
                                                ->relationship('penjual', 'nama')
                                                ->searchable()
                                                ->createOptionForm([
                                                    Forms\Components\TextInput::make('nama')
                                                        ->label('Nama Penjual')
                                                        ->required()
                                                        ->maxLength(255),
                                                    Forms\Components\TextInput::make('alamat')
                                                        ->label('Alamat')
                                                        ->maxLength(255),
                                                    Forms\Components\TextInput::make('telepon')
                                                        ->tel()
                                                        ->label('Nomor Telepon'),
                                                    // Hutang awal hanya muncul saat create
                                                    Forms\Components\TextInput::make('hutang')
                                                        ->label('Hutang Awal') // Ubah label
                                                        ->helperText('Masukkan hutang awal jika ada. Input ini hanya bisa dilakukan sekali saat pendaftaran penjual.')
                                                        ->prefix('Rp')
                                                        ->numeric()
                                                        ->default(0)
                                                        ->live(onBlur: true) // Tambahkan live update
                                                        ->currencyMask(
                                                            thousandSeparator: '.',
                                                            decimalSeparator: ',',
                                                            precision: 0
                                                        )
                                                        ->afterStateUpdated(function ($state, Forms\Set $set) {
                                                            // Update sisa hutang saat hutang awal berubah
                                                            $set('hutang_awal', $state);
                                                            $set('sisa_hutang_penjual', $state);
                                                            $set('pembayaran_hutang', 0);
                                                        }),
                                                ])
                                                ->createOptionAction(function (Forms\Components\Actions\Action $action) {
                                                    return $action
                                                        ->modalHeading('Tambah Penjual Baru')
                                                        ->modalWidth('lg')
                                                        ->successNotification(
                                                            Notification::make()
                                                                ->success()
                                                                ->duration(3000) // Set durasi 3 detik
                                                                ->persistent(false) // Notifikasi akan otomatis hilang
                                                                ->title('Penjual Berhasil Ditambahkan')
                                                                ->body('Data penjual dan hutang awal berhasil disimpan.')
                                                        );
                                                })
                                                ->afterStateUpdated(function ($state, Forms\Set $set) {
                                                    if ($state) {
                                                        // Get fresh data penjual
                                                        $penjual = Penjual::find($state);
                                                        if ($penjual) {
                                                            // Set hutang_awal dari data penjual
                                                            $set('hutang_awal', $penjual->hutang);
                                                            // Set sisa hutang awal sama dengan hutang_awal
                                                            $set('sisa_hutang_penjual', $penjual->hutang);
                                                            // Reset pembayaran hutang
                                                            $set('pembayaran_hutang', 0);
                                                        } else {
                                                            // Reset semua field terkait hutang
                                                            $set('hutang_awal', 0);
                                                            $set('sisa_hutang_penjual', 0);
                                                            $set('pembayaran_hutang', 0);
                                                        }
                                                    }
                                                })
                                                ->preload()
                                                ->live()
                                                ->afterStateUpdated(function ($state, Forms\Set $set, Forms\Get $get) {
                                                    if ($state && $get('kategori') === 'bayar_hutang') {
                                                        $penjual = Penjual::find($state);
                                                        if ($penjual) {
                                                            $set('info_hutang', "💰 Total Hutang: Rp " . number_format($penjual->hutang, 0, ',', '.'));
                                                            $set('hutang_awal', $penjual->hutang);
                                                            $set('max_pembayaran', $penjual->hutang);
                                                        }
                                                    }
                                                })
                                                ->visible(fn($get) => $get('tipe_nama') === 'penjual'),

                                            Forms\Components\Select::make('pekerja_id')
                                                ->label('Pilih Pekerja')
                                                ->relationship('pekerja', 'nama')
                                                ->searchable()
                                                ->preload()
                                                ->live()
                                                ->afterStateUpdated(function ($state, Forms\Set $set, Forms\Get $get) {
                                                    if ($state && $get('kategori') === 'bayar_hutang') {
                                                        $pekerja = Pekerja::find($state);
                                                        if ($pekerja) {
                                                            $set('info_hutang', "💰 Total Hutang: Rp " . number_format($pekerja->hutang, 0, ',', '.'));
                                                            $set('hutang_awal', $pekerja->hutang);
                                                            $set('max_pembayaran', $pekerja->hutang);
                                                        }
                                                    }
                                                })
                                                ->visible(fn($get) => $get('tipe_nama') === 'pekerja'),

                                            Forms\Components\Select::make('user_id')
                                                ->label('Pilih Karyawan')
                                                ->relationship('user', 'name')
                                                ->searchable()
                                                ->preload()
                                                ->live()
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
                            ]),

                        // Panel Keterangan & Bukti
                        Forms\Components\Section::make('Informasi Tambahan')
                            ->schema([
                                Forms\Components\Textarea::make('keterangan')
                                    ->label('Keterangan Transaksi')
                                    ->helperText('Tambahkan keterangan jika diperlukan')
                                    ->rows(2)
                                    ->columnSpan(1),

                                Forms\Components\FileUpload::make('file_bukti')
                                    ->label('Upload Bukti Transaksi')
                                    ->helperText('Upload foto/scan bukti transaksi (opsional)')
                                    ->image()
                                    ->imageEditor()
                                    ->disk('public')
                                    ->directory('bukti-operasional')
                                    ->openable()
                                    ->downloadable()
                                    ->columnSpan(1),
                            ])
                            ->columns(2)
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
