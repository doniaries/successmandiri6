<?php

namespace App\Filament\Resources;

use Carbon\Carbon;
use Filament\Forms;
use Filament\Tables;
use App\Models\Penjual;
use Filament\Forms\Form;
use Filament\Tables\Table;
use App\Models\TransaksiDo;
use Filament\Resources\Resource;
use Filament\Support\Colors\Color;
use Filament\Tables\Actions\Action;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Builder;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\TransaksiDoResource\Pages;
use App\Filament\Resources\TransaksiDoResource\Widgets\TransaksiDoStatWidget;
use Filament\Tables\Enums\ActionsPosition;
// use Barryvdh\DomPDF\Facade\Pdf;
// use SimpleSoftwareIO\QrCode\Facades\QrCode;


class TransaksiDoResource extends Resource
{
    protected static ?string $model = TransaksiDo::class;
    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $navigationLabel = 'Transaksi DO';
    protected static ?string $modelLabel = 'Transaksi DO';
    protected static ?string $pluralModelLabel = 'Transaksi DO';
    protected static ?int $navigationSort = 1;


    // public static function getNavigationBadge(): ?string
    // {
    //     return static::getModel()::count();
    // }

    public static function getWidgets(): array
    {
        return [
            TransaksiDoStatWidget::class,
        ];
    }

    public static function getModel(): string
    {
        return TransaksiDo::class;
    }



    public static function form(Form $form): Form
    {
        return $form->schema([
            // Header Section - Informasi Utama
            Forms\Components\Section::make()
                ->schema([

                    Forms\Components\Grid::make()
                        ->schema([
                            Forms\Components\TextInput::make('nomor')
                                ->label('Nomor DO')
                                ->default(function () {
                                    return TransaksiDo::generateMonthlyNumber();
                                })
                                ->disabled()
                                ->dehydrated(),

                            Forms\Components\DateTimePicker::make('tanggal')
                                ->label('Tanggal')
                                ->format('Y-m-d H:i:s')
                                ->native(false)
                                ->displayFormat('d/m/Y H:i:s')
                                ->default(Carbon::now()) // Menggunakan Carbon untuk nilai default
                                ->required()
                                ->disabled()
                                ->dehydrated(),

                        ])
                        ->columns(2),
                ])
                ->columnSpanFull(),

            // Detail Pengiriman Section
            Forms\Components\Grid::make()
                ->schema([
                    // Panel Kiri
                    Forms\Components\Section::make()
                        ->schema([
                            Forms\Components\Grid::make()
                                ->schema([
                                    Forms\Components\Select::make('penjual_id')
                                        ->label('Penjual')
                                        ->relationship('penjual', 'nama')
                                        ->searchable()
                                        ->debounce(500)
                                        ->preload()
                                        ->live()
                                        ->required()
                                        ->hint('+ tambahkan penjual baru')
                                        ->hintColor('primary')
                                        ->createOptionForm([
                                            Forms\Components\TextInput::make('nama')
                                                ->label('Nama Penjual')
                                                ->required()
                                                ->maxLength(255),

                                            Forms\Components\TextInput::make('alamat')
                                                ->label('Alamat')
                                                ->maxLength(255),

                                            Forms\Components\TextInput::make('telepon')
                                                ->label('Nomor Telepon')
                                                ->tel()
                                                ->maxLength(255),

                                            Forms\Components\TextInput::make('hutang')
                                                ->label('Total Hutang')
                                                ->disabled()
                                                ->dehydrated()
                                                ->prefix('Rp')
                                                ->numeric()
                                                ->default(0)
                                                ->currencyMask(
                                                    thousandSeparator: ',',
                                                    decimalSeparator: '.',
                                                    precision: 0
                                                ),
                                        ])
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
                                        }),

                                    Forms\Components\TextInput::make('nomor_polisi')
                                        ->placeholder('BA 1234 K')
                                        ->label('Nomor Polisi'),
                                    Forms\Components\TextInput::make('tonase')
                                        ->label('Tonase (Netto)')
                                        ->required()
                                        ->numeric()
                                        ->suffix('Kg')
                                        ->live(onBlur: true)
                                        ->afterStateUpdated(fn($state, Forms\Get $get, Forms\Set $set) =>
                                        static::hitungTotal($state, $get, $set)),

                                    Forms\Components\TextInput::make('harga_satuan')
                                        ->label('Harga Satuan')
                                        ->currencyMask(
                                            thousandSeparator: '.',
                                            decimalSeparator: ',',
                                            precision: 0
                                        )
                                        ->required()
                                        ->default(0)
                                        ->prefix('Rp')
                                        ->numeric()

                                        ->live(onBlur: true)
                                        ->afterStateUpdated(fn($state, Forms\Get $get, Forms\Set $set) =>
                                        static::hitungTotal($state, $get, $set)),
                                ])
                                ->columns(2),
                        ])
                        ->columnSpan(2),

                    // Panel Kanan
                    Forms\Components\Section::make()
                        ->schema([
                            Forms\Components\TextInput::make('hutang_awal')
                                ->label('Total Hutang')
                                ->currencyMask(
                                    thousandSeparator: '.',
                                    decimalSeparator: ',',
                                    precision: 0
                                )
                                ->prefix('Rp')
                                ->disabled()
                                ->dehydrated()
                                ->numeric()
                                ->default(0),
                            Forms\Components\TextInput::make('total')
                                ->label('Sub Total')
                                ->currencyMask(thousandSeparator: ',', decimalSeparator: '.', precision: 0)
                                ->prefix('Rp')
                                ->extraAttributes(['class' => 'text-xl font-bold text-primary-600'])
                                ->disabled()
                                ->dehydrated(),
                        ])
                        ->columnSpan(1),
                ])
                ->columns(3)
                ->columnSpanFull(),

            // Perhitungan & Pembayaran Section
            Forms\Components\Grid::make()
                ->schema([
                    // Panel Kiri - Detail Pembayaran
                    Forms\Components\Section::make()
                        ->schema([
                            Forms\Components\Grid::make()
                                ->schema([
                                    Forms\Components\TextInput::make('upah_bongkar')
                                        ->label('Upah Bongkar')
                                        ->hint('*jika ada')
                                        ->hintColor('primary')
                                        ->currencyMask(thousandSeparator: ',', decimalSeparator: '.', precision: 0)
                                        ->prefix('Rp')
                                        ->default(0)
                                        ->live(onBlur: true)
                                        ->afterStateUpdated(fn($state, Forms\Get $get, Forms\Set $set) =>
                                        static::hitungSisaBayar($state, $get, $set)),

                                    Forms\Components\TextInput::make('biaya_lain')
                                        ->label('Biaya Lain')
                                        ->hint('*jika ada')
                                        ->hintColor('primary')
                                        ->currencyMask(thousandSeparator: ',', decimalSeparator: '.', precision: 0)
                                        ->prefix('Rp')
                                        ->default(0)
                                        ->live(onBlur: true)
                                        ->afterStateUpdated(fn($state, Forms\Get $get, Forms\Set $set) =>
                                        static::hitungSisaBayar($state, $get, $set)),

                                    Forms\Components\TextInput::make('pembayaran_hutang')
                                        ->label('Bayar Hutang')
                                        ->currencyMask(
                                            thousandSeparator: '.',
                                            decimalSeparator: ',',
                                            precision: 0
                                        )
                                        ->prefix('Rp')
                                        ->default(0)
                                        ->live(onBlur: true)
                                        // Tambahkan kondisi visible
                                        ->visible(fn(Forms\Get $get): bool => $get('hutang_awal') > 0)
                                        ->afterStateUpdated(function ($state, Forms\Get $get, Forms\Set $set) {
                                            // Format values
                                            $hutangAwal = self::formatCurrency($get('hutang_awal'));
                                            $bayarHutang = self::formatCurrency($state);

                                            // Validasi pembayaran tidak melebihi hutang
                                            if ($bayarHutang > $hutangAwal) {
                                                $set('pembayaran_hutang', $hutangAwal);
                                                $bayarHutang = $hutangAwal;

                                                // Perbaikan format notifikasi
                                                Notification::make()
                                                    ->warning()
                                                    ->title('Pembayaran Hutang')  // Title lebih deskriptif
                                                    ->body(sprintf(
                                                        'Pembayaran disesuaikan menjadi Rp %s sesuai total hutang',
                                                        number_format($hutangAwal, 0, ',', '.')
                                                    ))
                                                    ->duration(3000)  // Durasi lebih singkat
                                                    ->persistent(false) // Non-persistent karena sudah auto close
                                                    ->color('warning')  // Tambah warna warning
                                                    ->icon('heroicon-o-banknotes') // Icon yang lebih sesuai
                                                    ->send();
                                            }

                                            // Update sisa hutang
                                            $sisaHutang = max(0, $hutangAwal - $bayarHutang);
                                            $set('sisa_hutang_penjual', $sisaHutang);

                                            // Hitung ulang sisa bayar
                                            $total = self::formatCurrency($get('total'));
                                            $upahBongkar = self::formatCurrency($get('upah_bongkar'));
                                            $biayaLain = self::formatCurrency($get('biaya_lain'));

                                            $sisaBayar = max(0, $total - $upahBongkar - $biayaLain - $bayarHutang);
                                            $set('sisa_bayar', $sisaBayar);
                                        }),

                                    Forms\Components\TextInput::make('keterangan_biaya_lain')
                                        ->label('Keterangan Biaya Lain')
                                        ->placeholder('uang jalan + ...'),
                                    Forms\Components\Select::make('status_bayar')
                                        ->label('Status Bayar')
                                        ->options([
                                            'Lunas' => 'Lunas',
                                            'Belum Lunas' => 'Belum Lunas',

                                        ])
                                        ->default('Lunas')
                                        ->required(),
                                    Forms\Components\Select::make('cara_bayar')
                                        ->label('Cara Bayar')
                                        ->options(TransaksiDo::CARA_BAYAR)
                                        ->default('Tunai')
                                        ->required()
                                        ->live()
                                        ->afterStateUpdated(function ($state, Forms\Get $get, Forms\Set $set) {
                                            if ($state !== 'Tunai') {
                                                // Reset validasi saldo untuk non-tunai
                                                $set('_tmp_bypass_saldo_check', true);
                                            }
                                        }),

                                    Forms\Components\TextInput::make('catatan')
                                        ->label('Catatan'),

                                    Forms\Components\FileUpload::make('file_do')
                                        ->label('Upload File DO')
                                        ->disk('public') // Tambahkan ini
                                        ->directory('do-files')
                                        ->preserveFilenames()
                                        ->acceptedFileTypes(['application/pdf', 'image/*'])
                                        ->openable() // Tambahkan ini
                                        ->downloadable() // Tambahkan ini
                                        ->previewable() // Tambahkan ini untuk PDF
                                        ->columnSpanFull(),


                                ])
                                ->columns(3),
                        ])
                        ->columnSpan(2),

                    // Panel Kanan - Informasi Hutang & Sisa
                    Forms\Components\Section::make()
                        ->schema([
                            Forms\Components\Grid::make()
                                ->schema([
                                    Forms\Components\TextInput::make('sisa_hutang_penjual')
                                        ->label('Sisa Hutang')
                                        ->currencyMask(
                                            thousandSeparator: '.',
                                            decimalSeparator: ',',
                                            precision: 0
                                        )
                                        ->prefix('Rp')
                                        ->disabled()
                                        ->dehydrated()
                                        ->default(0),

                                    Forms\Components\TextInput::make('sisa_bayar')
                                        ->label('Sisa Bayar')
                                        ->required()
                                        ->prefix('Rp')
                                        ->currencyMask(thousandSeparator: ',', decimalSeparator: '.', precision: 0)
                                        ->disabled()
                                        ->dehydrated(),


                                ])
                                ->columns(1),
                        ])
                        ->columnSpan(1),
                ])
                ->columns(3)
                ->columnSpanFull(),


        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->contentGrid([
                'md' => 2,
                'xl' => 3,
            ])
            ->columns([
                Tables\Columns\TextColumn::make('file_do') //image file do
                    ->label('File DO')
                    ->tooltip('klik untuk melihat')
                    ->alignCenter()
                    ->icon('heroicon-m-document')
                    ->color(Color::Emerald)
                    ->formatStateUsing(fn($state) => $state ? 'Lihat' : '-')
                    ->action(
                        Action::make('previewFile')
                            ->modalHeading('Preview File DO')
                            ->modalWidth('4xl')
                            ->modalContent(fn($record) => view(
                                'filament.components.file-viewer',
                                ['url' => Storage::url($record->file_do ?? '')]
                            ))
                    ),
                Tables\Columns\TextColumn::make('nomor')
                    ->label('Nomor')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->copyMessage('no DO telah disalin')
                    ->copyMessageDuration(1500)
                    ->badge()
                    ->color(Color::Blue),

                Tables\Columns\TextColumn::make('tanggal')
                    ->label('Tanggal')
                    ->badge()
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),

                Tables\Columns\TextColumn::make('penjual.nama')
                    ->label('Penjual')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('nomor_polisi')
                    ->label('Nomor Polisi')
                    ->searchable(),

                Tables\Columns\TextColumn::make('tonase')
                    ->label('Tonase')
                    ->suffix(' Kg')
                    ->summarize([
                        Tables\Columns\Summarizers\Sum::make()
                            ->suffix(' Kg')
                    ])
                    ->numeric()
                    ->sortable(),

                Tables\Columns\TextColumn::make('harga_satuan')
                    ->label('Harga Satuan')
                    ->money('IDR')
                    ->sortable(),

                Tables\Columns\TextColumn::make('total')
                    ->label('Total')
                    ->money('IDR')
                    ->color(Color::Amber)
                    ->weight('bold')
                    ->summarize([
                        Tables\Columns\Summarizers\Sum::make()
                            ->money('IDR')
                    ])
                    ->sortable(),

                Tables\Columns\TextColumn::make('upah_bongkar')
                    ->label('Upah Bongkar')
                    ->money('IDR')
                    ->summarize([
                        Tables\Columns\Summarizers\Sum::make()
                            ->money('IDR')
                    ])
                    ->sortable(),

                Tables\Columns\TextColumn::make('biaya_lain')
                    ->label('Biaya Lain')
                    ->summarize([
                        Tables\Columns\Summarizers\Sum::make()
                            ->money('IDR')
                    ])
                    ->money('IDR'),

                Tables\Columns\TextColumn::make('keterangan_biaya_lain')
                    ->hidden()
                    ->label('Keterangan Biaya Lain'),

                Tables\Columns\TextColumn::make('hutang_awal')
                    ->label('Hutang')
                    ->money('IDR')
                    ->color(Color::Red)
                    ->sortable(),

                Tables\Columns\TextColumn::make('pembayaran_hutang')
                    ->label('Bayar Hutang')
                    ->money('IDR')
                    ->summarize([
                        Tables\Columns\Summarizers\Sum::make()
                            ->money('IDR')
                    ])
                    ->color(Color::Orange)
                    ->sortable(),

                Tables\Columns\TextColumn::make('sisa_hutang_penjual')  // Sesuaikan dengan nama kolom di database
                    ->label('Sisa Hutang')
                    ->money('IDR')
                    ->state(function (TransaksiDo $record): int {
                        // Gunakan field sisa_hutang_penjual yang sudah dihitung
                        return $record->sisa_hutang_penjual ?? max(0, $record->hutang_awal - $record->pembayaran_hutang);
                    })
                    ->sortable()
                    ->summarize([
                        Tables\Columns\Summarizers\Sum::make()
                            ->money('IDR')
                    ])
                    ->alignRight() // Opsional: untuk alignment nominal uang
                    ->color(
                        fn(TransaksiDo $record): string =>
                        $record->sisa_hutang_penjual > 0 ? 'danger' : 'success'
                    ),

                Tables\Columns\TextColumn::make('sisa_bayar')
                    ->label('Sisa Bayar')
                    ->money('IDR')
                    ->color(Color::Emerald)
                    ->weight('bold')
                    ->summarize([
                        Tables\Columns\Summarizers\Sum::make()
                            ->money('IDR')
                    ])
                    ->sortable(),

                Tables\Columns\TextColumn::make('status_bayar')
                    ->label('Status Bayar')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'Lunas' => 'success',
                        'Belum Lunas' => 'warning',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('cara_bayar')
                    ->label('Cara Bayar')
                    ->searchable()
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'Tunai' => 'success',
                        'Transfer' => 'info',
                        'Cair di Luar' => 'warning',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->searchable()
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),


            ])
            ->defaultSort('created_at', 'desc')
            ->striped()
            ->paginated([5, 10, 25, 50, 100, 'all'])
            ->deferLoading()
            ->poll('5s')
            ->persistSortInSession()
            ->filters([
                Tables\Filters\Filter::make('created_at')
                    ->form([
                        Forms\Components\DatePicker::make('created_from')
                            ->label('Dari Tanggal')
                            ->format('d-m-Y')
                            ->native(false)
                            ->maxDate(now())
                            ->placeholder('Dari Tanggal')
                            ->required(),
                        Forms\Components\DatePicker::make('created_to')
                            ->label('Sampai Tanggal')
                            ->maxDate(now())
                            ->native(false)
                            ->placeholder('Sampai Tanggal')
                            ->required(),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['created_from'],
                                fn(Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date),
                            )
                            ->when(
                                $data['created_to'],
                                fn(Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date)
                            );
                    }),

                Tables\Filters\TrashedFilter::make(),
            ])
            ->filtersTriggerAction(
                fn(Tables\Actions\Action $action) => $action
                    ->button()
                    ->label('Filter Tanggal')
            )
            ->actions([
                Action::make('print')
                    ->label('Print')
                    ->icon('heroicon-o-printer')
                    ->color('success')
                    ->action(function (TransaksiDo $record) {
                        return response()->streamDownload(function () use ($record) {
                            echo $record->generatePdf();
                        }, "DO-{$record->nomor}.pdf");
                    })
                    ->requiresConfirmation()
                    ->modalHeading('Print Transaksi DO')
                    ->modalDescription('Apakah Anda yakin ingin mencetak transaksi ini?')
                    ->modalSubmitActionLabel('Ya, Cetak')
                    ->modalIcon('heroicon-o-printer'),
                Tables\Actions\EditAction::make(),
            ])

            ->actionsPosition(ActionsPosition::BeforeColumns)
            // Tambahkan konfigurasi action lainnya jika diperlukan
            ->actionsColumnLabel('Actions') // Optional: untuk memberi label kolom action
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
                Tables\Actions\ForceDeleteBulkAction::make(),
                Tables\Actions\RestoreBulkAction::make(),

                // ]),
            ])
            ->emptyStateHeading('Belum ada data Transaksi DO')
            ->emptyStateDescription('Silakan tambah Transaksi DO baru dengan klik tombol di atas')
            ->emptyStateIcon('heroicon-o-banknotes');
    }

    // Tambahan untuk memastikan data diload dengan benar
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ])
            ->select(['transaksi_do.*']) // Pastikan semua kolom yang dibutuhkan
            ->with(['penjual']); // Eager load relasi yang dibutuhkan
    }


    // Helper method untuk update sisa bayar
    private static function updateSisaBayar(Forms\Get $get, Forms\Set $set): void
    {
        $total = $get('total') ?? 0;
        $upahBongkar = $get('upah_bongkar') ?? 0;
        $biayaLain = $get('biaya_lain') ?? 0;
        $pembayaranHutang = $get('pembayaran_hutang') ?? 0;

        $sisaBayar = $total - $upahBongkar - $biayaLain - $pembayaranHutang;
        $set('sisa_bayar', max(0, $sisaBayar));
    }

    //---------------------------------//


    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTransaksiDos::route('/'),
            'create' => Pages\CreateTransaksiDo::route('/create'),
            'edit' => Pages\EditTransaksiDo::route('/{record}/edit'),
        ];
    }



    public static function getNavigationBadgeColor(): ?string
    {
        return 'primary';
    }

    // Helper methods untuk kalkulasi
    private static function formatCurrency($number): int
    {
        if (empty($number)) return 0;
        // Handle string format currency
        if (is_string($number)) {
            return (int) str_replace(['.', ','], ['', '.'], $number);
        }
        return (int) $number;
    }

    //-----------------------------//
    // Helper methods untuk kalkulasi
    private static function hitungTotal($state, Forms\Get $get, Forms\Set $set): void
    {
        // Format values
        $tonase = self::formatCurrency($get('tonase'));
        $hargaSatuan = self::formatCurrency($get('harga_satuan'));

        if ($tonase && $hargaSatuan) {
            // Calculate total
            $total = $tonase * $hargaSatuan;
            $set('total', $total);

            // Recalculate sisa bayar
            $upahBongkar = self::formatCurrency($get('upah_bongkar'));
            $biayaLain = self::formatCurrency($get('biaya_lain'));
            $bayarHutang = self::formatCurrency($get('pembayaran_hutang'));

            // Total - (Upah Bongkar + Biaya Lain + Bayar Hutang)
            $sisaBayar = $total - $upahBongkar - $biayaLain - $bayarHutang;
            $set('sisa_bayar', max(0, $sisaBayar));
        }
    }



    // ---------------------//

    // Perbaikan logika bayar hutang
    private static function hitungPembayaranHutang($state, Forms\Get $get, Forms\Set $set): void
    {
        // Format values
        $hutang = self::formatCurrency($get('hutang_awal'));
        $bayarHutang = self::formatCurrency($state);

        // Validate bayar hutang
        if ($bayarHutang > $hutang_awal) {
            $bayarHutang = $hutang_awal;
            $set('pembayaran_hutang', $hutang_awal);

            // Perbaikan format notifikasi
            Notification::make()
                ->warning()
                ->title('Pembayaran Hutang')
                ->body(sprintf(
                    'Pembayaran disesuaikan menjadi Rp %s sesuai total hutang',
                    number_format($hutang_awal, 0, ',', '.')
                ))
                ->duration(3000)
                ->persistent(false)
                ->color('warning')
                ->icon('heroicon-o-banknotes')
                ->send();
        }

        // Update sisa hutang
        $sisaHutang = $hutang_awal - $bayarHutang;
        $set('sisa_hutang_penjual', max(0, $sisaHutang));

        // Recalculate sisa bayar
        $total = self::formatCurrency($get('total'));
        $upahBongkar = self::formatCurrency($get('upah_bongkar'));
        $biayaLain = self::formatCurrency($get('biaya_lain'));

        // Sisa Bayar = Total - (Upah Bongkar + Biaya Lain + Bayar Hutang)
        $sisaBayar = $total - $upahBongkar - $biayaLain - $bayarHutang;
        $set('sisa_bayar', max(0, $sisaBayar));
    }


    private static function hitungSisaBayar($state, Forms\Get $get, Forms\Set $set): void
    {
        // Format values
        $total = self::formatCurrency($get('total'));
        $upahBongkar = self::formatCurrency($get('upah_bongkar'));
        $biayaLain = self::formatCurrency($get('biaya_lain'));
        $bayarHutang = self::formatCurrency($get('pembayaran_hutang'));

        // Sisa Bayar = Total - (Upah Bongkar + Biaya Lain + Bayar Hutang)
        $sisaBayar = $total - $upahBongkar - $biayaLain - $bayarHutang;
        $set('sisa_bayar', max(0, $sisaBayar));
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Format numeric fields
        $numericFields = [
            'tonase',
            'harga_satuan',
            'upah_bongkar',
            'biaya_lain',
            'pembayaran_hutang'
        ];

        foreach ($numericFields as $field) {
            $data[$field] = self::formatCurrency($data[$field] ?? 0);
        }

        // Get fresh hutang from penjual
        if (!empty($data['penjual_id'])) {
            $penjual = Penjual::find($data['penjual_id']);
            if ($penjual) {
                $data['hutang_awal'] = $penjual->hutang_awal;

                // Revalidate pembayaran_hutang
                if ($data['pembayaran_hutang'] > $data['hutang_awal']) {
                    $data['pembayaran_hutang'] = $data['hutang_awal'];
                }
            }
        }

        // Calculate derived values
        $data['total'] = $data['tonase'] * $data['harga_satuan'];
        $data['sisa_hutang_penjual'] = max(0, $data['hutang_awal'] - $data['pembayaran_hutang']);
        $data['sisa_bayar'] = max(0, $data['total'] - $data['upah_bongkar'] - $data['biaya_lain'] - $data['pembayaran_hutang']);

        return $data;
    }
}
