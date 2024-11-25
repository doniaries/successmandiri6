<?php

namespace App\Filament\Resources;

use Carbon\Carbon;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Notifications\Notification;
use Filament\Support\Exceptions\Halt;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use App\Models\LaporanKeuangan;
use Barryvdh\DomPDF\Facade\Pdf;
use Filament\Resources\Resource;
use Filament\Tables\Filters\Filter;
use Filament\Actions\Action;
use Filament\Tables\Actions\Action as TableAction;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
use App\Services\LaporanKeuanganService;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\LaporanKeuanganResource\Pages;
use App\Filament\Resources\LaporanKeuanganResource\RelationManagers;
use App\Filament\Resources\LaporanKeuanganResource\Widgets\LaporanKeuanganDoStatsWidget;
use App\Filament\Resources\TransaksiDoResource\Widgets\TransaksiDoStatWidget;

class LaporanKeuanganResource extends Resource
{
    protected static ?string $model = LaporanKeuangan::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?int $navigationSort = 2;


    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\DateTimePicker::make('tanggal')
                    ->required(),
                Forms\Components\TextInput::make('jenis_transaksi')
                    ->required(),
                Forms\Components\TextInput::make('kategori')
                    ->required()
                    ->maxLength(50),
                Forms\Components\TextInput::make('sub_kategori')
                    ->maxLength(50),
                Forms\Components\TextInput::make('nominal')
                    ->required()
                    ->numeric(),
                Forms\Components\TextInput::make('sumber_transaksi')
                    ->required()
                    ->maxLength(50),
                Forms\Components\TextInput::make('referensi_id')
                    ->required()
                    ->numeric(),
                Forms\Components\TextInput::make('nomor_referensi')
                    ->maxLength(50),
                Forms\Components\TextInput::make('pihak_terkait')
                    ->maxLength(100),
                Forms\Components\TextInput::make('tipe_pihak'),
                Forms\Components\TextInput::make('cara_pembayaran')
                    ->maxLength(20),
                Forms\Components\Textarea::make('keterangan')
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table

            ->columns([
                Tables\Columns\TextColumn::make('tanggal')
                    ->dateTime()
                    ->dateTime('d/M/Y H:i')
                    ->label('Tanggal Transaksi')
                    ->sortable(),
                Tables\Columns\TextColumn::make('jenis_transaksi')
                    ->badge()
                    ->searchable(),

                Tables\Columns\TextColumn::make('kategori')
                    ->searchable(),
                Tables\Columns\TextColumn::make('sub_kategori')
                    ->searchable(),
                Tables\Columns\TextColumn::make('nominal')
                    ->numeric()
                    ->prefix('Rp. ')
                    ->summarize([
                        Tables\Columns\Summarizers\Sum::make()
                            ->prefix('Rp. ')
                    ])
                    ->sortable(),
                Tables\Columns\TextColumn::make('sumber_transaksi')
                    ->label('dari')
                    ->searchable(),
                Tables\Columns\TextColumn::make('referensi_id')
                    // ->numeric()
                    ->hidden()
                    ->sortable(),
                Tables\Columns\TextColumn::make('nomor_referensi')
                    ->label('Nomor')
                    ->badge()
                    ->sortable()
                    ->copyable()
                    ->copyMessage('Nomor DO berhasil disalin')
                    ->searchable(),
                Tables\Columns\TextColumn::make('pihak_terkait')
                    ->badge()
                    ->searchable(),
                Tables\Columns\TextColumn::make('tipe_pihak'),
                Tables\Columns\TextColumn::make('cara_pembayaran')
                    ->searchable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('deleted_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            //download PDF//
            ->headerActions([
                Tables\Actions\Action::make('downloadPdf')
                    ->label('Download PDF')
                    ->icon('heroicon-o-document-arrow-down')
                    ->form([
                        Forms\Components\Grid::make()
                            ->schema([
                                Forms\Components\DatePicker::make('start_date')
                                    ->label('Dari Tanggal')
                                    ->required()
                                    ->default(now())
                                    ->displayFormat('d/m/Y')
                                    // ->default(now()->startOfMonth()),
                                    ->native(false),
                                Forms\Components\DatePicker::make('end_date')
                                    ->label('Sampai Tanggal')
                                    ->required()
                                    ->default(now())
                                    ->displayFormat('d/m/Y')
                                    ->native(false)
                                    ->minDate(fn($get) => $get('start_date')),
                            ])
                            ->columns(2)
                    ])
                    ->action(function (array $data) {
                        try {
                            $startDate = Carbon::parse($data['start_date'])->startOfDay();
                            $endDate = Carbon::parse($data['end_date'])->endOfDay();


                            // Get laporan data dari service
                            $laporanService = app(LaporanKeuanganService::class);
                            $laporanData = $laporanService->getLaporanData($startDate, $endDate);

                            // Get perusahaan data
                            $perusahaan = \App\Models\Perusahaan::firstOrFail();

                            // Hitung saldo awal (saldo saat ini dikurangi mutasi periode ini)
                            $saldoAwal = $perusahaan->saldo - ($laporanData['totalPemasukan'] - $laporanData['totalPengeluaran']);


                            // Prepare additional data
                            $viewData = array_merge($laporanData, [
                                'startDate' => $startDate,
                                'endDate' => $endDate,
                                'perusahaan' => $perusahaan,
                                'saldoAwal' => $saldoAwal, // Tambahkan saldo awal
                                'saldoAkhir' => $perusahaan->saldo, // Gunakan saldo terkini
                                'user' => auth()->user(),
                                'jenisTransaksi' => [
                                    'Pemasukan' => 'success',
                                    'Pengeluaran' => 'danger'
                                ]
                            ]);

                            // Get transaksi data
                            $transaksi = LaporanKeuangan::whereBetween('tanggal', [$startDate, $endDate])
                                ->get()
                                ->map(fn($item) => [
                                    'tanggal' => $item->tanggal,
                                    'nomor_referensi' => $item->nomor_referensi,
                                    'jenis_transaksi' => $item->jenis_transaksi,
                                    'sub_kategori' => $item->sub_kategori,
                                    'pihak_terkait' => $item->pihak_terkait,
                                    'cara_pembayaran' => $item->cara_pembayaran,
                                    'nominal' => $item->nominal,
                                ]);

                            // Get totals
                            $totalPemasukan = $transaksi
                                ->where('jenis_transaksi', 'Pemasukan')
                                ->sum('nominal');

                            $totalPengeluaran = $transaksi
                                ->where('jenis_transaksi', 'Pengeluaran')
                                ->sum('nominal');

                            // Generate PDF
                            $pdf = Pdf::loadView('laporan.keuangan-harian', $viewData);
                            $pdf->setPaper('a4', 'landscape');

                            return response()->streamDownload(
                                fn() => print($pdf->output()),
                                "laporan-keuangan-{$startDate->format('Y-m-d')}-{$endDate->format('Y-m-d')}.pdf"
                            );
                        } catch (\Exception $e) {
                            Notification::make()
                                ->danger()
                                ->duration(3000) // Set durasi 3 detik
                                ->persistent(false) // Notifikasi akan otomatis hilang
                                ->title('Error')
                                ->body('Gagal membuat laporan: ' . $e->getMessage())
                                ->send();
                        }
                    })
            ])
            // Filter tanggal
            ->filters([
                // Jenis Transaksi Filter
                SelectFilter::make('jenis_transaksi')
                    ->options([
                        'Pemasukan' => 'Pemasukan',
                        'Pengeluaran' => 'Pengeluaran'
                    ])
                    ->placeholder('Pilih Jenis Transaksi')
                    ->label('Jenis Transaksi'),

                // Date Range Filter
                Filter::make('date_range')
                    ->form([
                        Forms\Components\DatePicker::make('dari_tanggal')
                            ->label('Dari Tanggal')
                            ->displayFormat('d/m/Y') // Format tampilan
                            ->format('Y-m-d') // Format yang disimpan
                            ->native(false)
                            ->default(now()->startOfMonth()),
                        Forms\Components\DatePicker::make('sampai_tanggal')
                            ->label('Ke Tanggal')
                            ->displayFormat('d/m/Y') // Format tampilan
                            ->format('Y-m-d') // Format yang disimpan
                            ->native(false)
                            ->default(now())
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['dari_tanggal'],
                                fn(Builder $query, $date): Builder => $query->whereDate('tanggal', '>=', $date),
                            )
                            ->when(
                                $data['sampai_tanggal'],
                                fn(Builder $query, $date): Builder => $query->whereDate('tanggal', '<=', $date),
                            );
                    }),

                // Trash Filter
                Tables\Filters\TrashedFilter::make()
            ], layout: FiltersLayout::Modal)
            // ->filtersFormColumns(2)
            ->filtersTriggerAction(
                fn(Tables\Actions\Action $action) => $action
                    ->button()
                    ->label('Filter Tanggal')
            )
            ->defaultSort('created_at', 'desc')
            ->filtersFormColumns(2)
            ->actions([
                Tables\Actions\ViewAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    // Tables\Actions\DeleteBulkAction::make(),
                    // Tables\Actions\ForceDeleteBulkAction::make(),
                    // Tables\Actions\RestoreBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->striped()
            ->paginated([5, 10, 25, 50, 100, 'all'])
            ->deferLoading()
            ->poll('15s');
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
            'index' => Pages\ListLaporanKeuangans::route('/'),
            // 'create' => Pages\CreateLaporanKeuangan::route('/create'),
            // 'edit' => Pages\EditLaporanKeuangan::route('/{record}/edit'),
            'view' => Pages\ViewLaporanKeuangan::route('/{record}'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
    // Override canCreate untuk mencegah pembuatan data
    public static function canCreate(): bool
    {
        return false;
    }

    public static function getWidgets(): array
    {
        return [
            // LaporanKeuanganDoStatsWidget::class,
            TransaksiDoStatWidget::class,
        ];
    }
    public static function getNavigationBadgeColor(): ?string
    {
        return 'primary';
    }

    // public static function getNavigationBadge(): ?string
    // {
    //     return static::getModel()::count();
    // }
}
