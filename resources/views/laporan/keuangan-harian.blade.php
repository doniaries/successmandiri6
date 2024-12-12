<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Keuangan Harian - {{ $perusahaan->name }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: Arial, sans-serif;
        }

        body {
            padding: 30px;
            /* Margin dari tepi kertas */
            font-size: 10px;
            /* Ukuran font default lebih kecil */
            line-height: 1.3;
        }

        .report-header {
            text-align: center;
            margin-bottom: 25px;
        }

        .report-header h2 {
            font-size: 14px;
            margin-bottom: 3px;
        }

        .report-header h3 {
            font-size: 12px;
            margin-bottom: 3px;
        }

        .report-header p {
            font-size: 11px;
        }

        .summary-header {
            width: 100%;
            margin-bottom: 20px;
            border-collapse: collapse;
        }

        .summary-header td {
            border: 1px solid black;
            padding: 6px;
            text-align: center;
            font-weight: bold;
            font-size: 11px;
        }

        .main-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
            font-size: 9px;
            /* Ukuran font tabel utama */
        }

        .main-table th,
        .main-table td {
            border: 1px solid black;
            padding: 3px 4px;
        }

        .main-table th {
            background-color: #f0f0f0;
            font-weight: bold;
            text-align: center;
            font-size: 9px;
        }

        .amount {
            text-align: right;
            white-space: nowrap;
        }

        .total-row {
            font-weight: bold;
            background-color: #f0f0f0;
        }

        .operational-title {
            font-weight: bold;
            margin-bottom: 5px;
            font-size: 11px;
        }

        .operational-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 9px;
        }

        .operational-table th,
        .operational-table td {
            border: 1px solid black;
            padding: 3px 4px;
        }

        .header-section {
            font-weight: bold;
            background-color: #f0f0f0;
        }

        @page {
            margin: 20px;
            /* Margin halaman saat print */
        }

        @media print {
            body {
                padding: 20px;
            }
        }
    </style>
</head>

<body>
    <!-- Report Header -->
    <div class="report-header">
        <h2>{{ $perusahaan->name }}</h2>
        <h3>LAPORAN KEUANGAN HARIAN</h3>
        <p>{{ Carbon\Carbon::parse($tanggal)->isoFormat('dddd, D MMMM Y') }}</p>
    </div>

    <!-- Summary Header -->
    <table class="summary-header">
        <tr>
            <td style="width: 33%;">SISA SALDO<br>Rp {{ number_format($saldoAwal, 0, ',', '.') }}</td>
            <td style="width: 33%;">TOTAL SALDO/UANG MASUK<br>Rp {{ number_format($totalPemasukan, 0, ',', '.') }}</td>
            <td style="width: 33%;">PENGELUARAN/UANG KELUAR<br>Rp {{ number_format($totalPengeluaran, 0, ',', '.') }}
            </td>
        </tr>
    </table>

    <!-- Main Transactions Table -->
    <table class="main-table">
        <thead>
            <tr>
                <th style="width: 3%;">NO</th>
                <th style="width: 12%;">PENJUAL</th>
                <th style="width: 10%;">SUPIR</th>
                <th style="width: 8%;">TONASE</th>
                <th style="width: 8%;">HARGA</th>
                <th style="width: 10%;">SUB TOTAL</th>
                <th style="width: 8%;">BIAYA</th>
                <th style="width: 8%;">BAYAR HUTANG</th>
                <th style="width: 8%;">TUNAI</th>
                <th style="width: 8%;">TRANSFER</th>
                <th style="width: 8%;">CAIR DILUAR</th>
                <th style="width: 8%;">BELUM DIBAYAR</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($transaksiDo as $index => $transaksi)
                <tr>
                    <td class="amount">{{ $index + 1 }}</td>
                    <td>{{ $transaksi->penjual->nama ?? '-' }}</td>
                    <td>{{ $transaksi->supir->nama ?? '-' }}</td>
                    <td class="amount">{{ number_format($transaksi->tonase, 0) }}</td>
                    <td class="amount">{{ number_format($transaksi->harga_satuan, 0) }}</td>
                    <td class="amount">{{ number_format($transaksi->sub_total, 0) }}</td>
                    <td class="amount">{{ number_format($transaksi->biaya_lain + $transaksi->upah_bongkar, 0) }}</td>
                    <td class="amount">{{ number_format($transaksi->pembayaran_hutang, 0) }}</td>
                    <td class="amount">
                        {{ strtolower($transaksi->cara_bayar) === 'tunai' ? number_format($transaksi->sisa_bayar, 0) : '' }}
                    </td>
                    <td class="amount">
                        {{ strtolower($transaksi->cara_bayar) === 'transfer' ? number_format($transaksi->sisa_bayar, 0) : '' }}
                    </td>
                    <td class="amount">
                        {{ strtolower($transaksi->cara_bayar) === 'cair di luar' ? number_format($transaksi->sisa_bayar, 0) : '' }}
                    </td>
                    <td class="amount">
                        {{ strtolower($transaksi->cara_bayar) === 'belum Dibayar' ? number_format($transaksi->sisa_bayar, 0) : '' }}
                    </td>
                </tr>
            @endforeach
            <!-- Total Row -->
            <tr class="total-row">
                <td colspan="3">TOTAL</td>
                <td class="amount">{{ number_format($totalTonase, 0) }}</td>
                <td></td>
                <td class="amount">{{ number_format($totalSubTotal, 0) }}</td>
                <td class="amount">{{ number_format($totalBiaya, 0) }}</td>
                <td class="amount">{{ number_format($totalBayarHutang, 0) }}</td>
                <td class="amount">{{ number_format($pembayaran['tunai'], 0) }}</td>
                <td class="amount">{{ number_format($pembayaran['transfer'], 0) }}</td>
                <td class="amount">{{ number_format($pembayaran['cairDiluar'], 0) }}</td>
                <td class="amount">{{ number_format($pembayaran['belumDiBayar'], 0) }}</td>
            </tr>
        </tbody>
    </table>



    <!-- Operasional Section -->
    <!-- resources/views/laporan/keuangan-harian.blade.php -->

    <!-- Perbaiki bagian Operasional section -->
    <div class="operational-title">OPERASIONAL</div>
    <table class="operational-table">
        <tr>
            <td colspan="2" class="header-section">PEMASUKAN</td>
            <td colspan="2" class="header-section">PENGELUARAN</td>
        </tr>

        @php
            // Move this outside of transaksiDo loop
            $pemasukan = $operasional->where('operasional', 'pemasukan')->values();
            $pengeluaran = $operasional->where('operasional', 'pengeluaran')->values();
            $maxRows = max($pemasukan->count(), $pengeluaran->count());
        @endphp

        @if ($maxRows == 0)
            <tr>
                <td colspan="4" style="text-align: center;">Tidak ada data operasional untuk periode ini</td>
            </tr>
        @else
            @foreach (range(0, $maxRows - 1) as $i)
                <tr>
                    <!-- Pemasukan -->
                    @if ($i < $pemasukan->count())
                        <td style="width: 25%;">
                            {{ strtoupper($pemasukan[$i]->kategoriLabel) }}
                            @if ($pemasukan[$i]->keterangan)
                                <br><small>{{ $pemasukan[$i]->keterangan }}</small>
                            @endif
                        </td>
                        <td style="width: 25%;" class="amount">
                            Rp {{ number_format($pemasukan[$i]->nominal, 0) }}
                        </td>
                    @else
                        <td style="width: 25%;"></td>
                        <td style="width: 25%;"></td>
                    @endif

                    <!-- Pengeluaran -->
                    @if ($i < $pengeluaran->count())
                        <td style="width: 25%;">
                            {{ strtoupper($pengeluaran[$i]->kategoriLabel) }}
                            @if ($pengeluaran[$i]->keterangan)
                                <br><small>{{ $pengeluaran[$i]->keterangan }}</small>
                            @endif
                        </td>
                        <td style="width: 25%;" class="amount">
                            Rp {{ number_format($pengeluaran[$i]->nominal, 0) }}
                        </td>
                    @else
                        <td style="width: 25%;"></td>
                        <td style="width: 25%;"></td>
                    @endif
                </tr>
            @endforeach

            <tr class="total-row">
                <td>TOTAL</td>
                <td class="amount">Rp {{ number_format($pemasukan->sum('nominal'), 0) }}</td>
                <td>TOTAL</td>
                <td class="amount">Rp {{ number_format($pengeluaran->sum('nominal'), 0) }}</td>
            </tr>
        @endif
    </table>
</body>

</html>
