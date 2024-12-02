<?php

namespace App\Filament\Resources\TransaksiDoResource\Widgets;

use App\Models\{TransaksiDo, Perusahaan, LaporanKeuangan};
use Filament\Widgets\StatsOverviewWidget\Stat;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Carbon\Carbon;
use Illuminate\Support\Facades\{DB, Log};
use Livewire\Attributes\On;

class TransaksiDoStatWidget extends BaseWidget
{
    protected static ?int $sort = 1;
    protected static ?string $pollingInterval = '5s';
    protected static bool $isLazy = true;
    protected int | string | array $columnSpan = 'full';

    protected function getStats(): array
    {
        try {
            // [UPDATE] Get perusahaan data
            $perusahaan = Perusahaan::first();
            if (!$perusahaan) {
                throw new \Exception('Data perusahaan tidak ditemukan');
            }

            // [UPDATE] 1. Hitung Total Saldo/Uang Masuk
            $totalSaldoMasuk = DB::transaction(function () {
                // a. Saldo dari pimpinan
                $saldoPimpinan = DB::table('laporan_keuangan')
                    ->whereNull('deleted_at')
                    ->where('kategori', 'Saldo')
                    ->where('sub_kategori', 'Tambah Saldo')
                    ->sum('nominal');

                // b. Bayar hutang (dari DO & Operasional)
                $bayarHutang = DB::table('laporan_keuangan')
                    ->whereNull('deleted_at')
                    ->where('sub_kategori', 'Bayar Hutang')
                    ->where('cara_pembayaran', 'Tunai')
                    ->sum('nominal');

                // c. Pemasukan lain (upah bongkar & biaya lain)
                $pemasukanLain = DB::table('laporan_keuangan')
                    ->whereNull('deleted_at')
                    ->where('jenis_transaksi', 'Pemasukan')
                    ->whereIn('sub_kategori', ['Upah Bongkar', 'Biaya Lain'])
                    ->where('cara_pembayaran', 'Tunai')
                    ->sum('nominal');

                return $saldoPimpinan + $bayarHutang + $pemasukanLain;
            });

            // [UPDATE] 2. Hitung Pengeluaran/Uang Keluar
            $totalPengeluaran = DB::transaction(function () {
                return DB::table('laporan_keuangan')
                    ->whereNull('deleted_at')
                    ->where('jenis_transaksi', 'Pengeluaran')
                    ->whereIn('kategori', ['DO', 'Operasional'])
                    ->where('cara_pembayaran', 'Tunai')
                    ->sum('nominal');
            });

            // [UPDATE] 3. Get sisa saldo dari tabel perusahaan
            $sisaSaldo = $perusahaan->saldo;

            // Get detail untuk deskripsi
            $detailPemasukan = [
                'saldo_pimpinan' => DB::table('laporan_keuangan')
                    ->whereNull('deleted_at')
                    ->where('kategori', 'Saldo')
                    ->where('sub_kategori', 'Tambah Saldo')
                    ->sum('nominal'),

                'bayar_hutang' => DB::table('laporan_keuangan')
                    ->whereNull('deleted_at')
                    ->where('sub_kategori', 'Bayar Hutang')
                    ->where('cara_pembayaran', 'Tunai')
                    ->sum('nominal'),

                'pemasukan_lain' => DB::table('laporan_keuangan')
                    ->whereNull('deleted_at')
                    ->where('jenis_transaksi', 'Pemasukan')
                    ->whereIn('sub_kategori', ['Upah Bongkar', 'Biaya Lain'])
                    ->where('cara_pembayaran', 'Tunai')
                    ->sum('nominal')
            ];

            $detailPengeluaran = [
                'transaksi_do' => DB::table('laporan_keuangan')
                    ->whereNull('deleted_at')
                    ->where('kategori', 'DO')
                    ->where('jenis_transaksi', 'Pengeluaran')
                    ->where('cara_pembayaran', 'Tunai')
                    ->sum('nominal'),

                'operasional' => DB::table('laporan_keuangan')
                    ->whereNull('deleted_at')
                    ->where('kategori', 'Operasional')
                    ->where('jenis_transaksi', 'Pengeluaran')
                    ->where('cara_pembayaran', 'Tunai')
                    ->sum('nominal')
            ];

            // Log for monitoring
            Log::info('Stat Widget Values:', [
                'total_pemasukan' => $totalSaldoMasuk,
                'total_pengeluaran' => $totalPengeluaran,
                'sisa_saldo' => $sisaSaldo,
                'detail_pemasukan' => $detailPemasukan,
                'detail_pengeluaran' => $detailPengeluaran,
            ]);

            return [
                Stat::make('Sisa Saldo', 'Rp ' . number_format($sisaSaldo, 0, ',', '.'))
                    ->description('Saldo tersisa berdasarkan data di perusahaan')
                    ->color($this->getSaldoColor($sisaSaldo))
                    ->chart([7, 2, 10, 3, 15, 4, 17])
                    ->extraAttributes(['class' => 'font-bold']),

                Stat::make('Total Saldo/Uang Masuk', 'Rp ' . number_format($totalSaldoMasuk, 0, ',', '.'))
                    ->description(sprintf(
                        "Tambah Saldo: Rp %s\nBayar Hutang: Rp %s\nPemasukan Lain: Rp %s",
                        number_format($detailPemasukan['saldo_pimpinan'], 0, ',', '.'),
                        number_format($detailPemasukan['bayar_hutang'], 0, ',', '.'),
                        number_format($detailPemasukan['pemasukan_lain'], 0, ',', '.')
                    ))
                    ->color('success')
                    ->chart([7, 2, 10, 3, 15, 4, 17]),

                Stat::make('Pengeluaran/Uang Keluar', 'Rp ' . number_format($totalPengeluaran, 0, ',', '.'))
                    ->description(sprintf(
                        "Transaksi DO: Rp %s\nOperasional: Rp %s",
                        number_format($detailPengeluaran['transaksi_do'], 0, ',', '.'),
                        number_format($detailPengeluaran['operasional'], 0, ',', '.')
                    ))
                    ->color('danger')
                    ->chart([7, 2, 10, 3, 15, 4, 17]),
            ];
        } catch (\Exception $e) {
            Log::error('Error TransaksiDoStatWidget:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTrace()
            ]);

            return [
                Stat::make('Error', 'Terjadi kesalahan memuat data')
                    ->description($e->getMessage())
                    ->color('danger')
            ];
        }
    }

    private function getSaldoColor($saldo): string
    {
        return match (true) {
            $saldo > 50000000 => 'success',
            $saldo > 10000000 => 'info',
            $saldo > 0 => 'warning',
            default => 'danger'
        };
    }

    #[On(['refresh-widget', 'transaksi-created', 'transaksi-updated', 'transaksi-deleted', 'saldo-updated'])]
    public function refresh(): void
    {
        $this->getStats();
    }
}
