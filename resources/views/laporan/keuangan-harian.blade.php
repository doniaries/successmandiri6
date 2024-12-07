<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Keuangan Harian - {{ $perusahaan->nama }}</title>
    <style>
        /* Reset */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        /* Base */
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            padding: 20px;
        }

        /* Header */
        .header {
            text-align: center;
            margin-bottom: 30px;
            padding: 20px 0;
            border-bottom: 2px solid #ddd;
        }

        .header img {
            max-height: 60px;
            margin-bottom: 15px;
        }

        .header h1 {
            font-size: 20px;
            margin-bottom: 5px;
            font-weight: bold;
            color: #333;
        }

        .header h2 {
            font-size: 18px;
            margin: 10px 0;
            font-weight: bold;
            color: #444;
        }

        .header p {
            font-size: 14px;
            color: #666;
            margin: 5px 0;
        }

        .header .alamat {
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 10px;
            color: #333;
        }

        .header .tanggal {
            color: #666;
        }

        /* Table */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            font-size: 14px;
        }

        th,
        td {
            padding: 8px;
            border: 1px solid #ddd;
            text-align: left;
        }

        th {
            background-color: #f5f5f5;
            font-weight: bold;
        }

        /* Summary */
        .summary {
            margin-top: 20px;
            padding: 15px;
            background-color: #f9f9f9;
            border-radius: 5px;
        }

        .summary h2 {
            font-size: 18px;
            margin-bottom: 10px;
        }

        .summary p {
            margin-bottom: 5px;
        }

        /* Utilities */
        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        .text-bold {
            font-weight: bold;
        }

        .bg-light {
            background-color: #f8f9fa;
        }

        .text-success {
            color: #28a745;
        }

        .text-danger {
            color: #dc3545;
        }

        .mt-4 {
            margin-top: 20px;
        }

        .mb-4 {
            margin-bottom: 20px;
        }

        @media print {
            body {
                padding: 0;
                margin: 0;
            }

            .no-print {
                display: none;
            }
        }
    </style>
</head>

<body>
    <!-- Header -->
    <div class="header">
        {{-- <img src="{{ $perusahaan->logo }}" alt="Logo"> --}}
        <h2>{{ $perusahaan->name }}</h2>
        <h2>Laporan Keuangan Harian</h2>
        <p class="tanggal">Tanggal: {{ Carbon\Carbon::parse($startDate)->isoFormat('dddd, D MMMM Y') }}</p>
    </div>

    <!-- Transaksi DO -->
    <div class="mt-4">
        <h3>Transaksi DO</h3>
        <table>
            <thead>
                <tr>
                    <th>No.</th>
                    <th>Nomor DO</th>
                    <th>Penjual</th>
                    <th>Tonase</th>
                    <th>Cara Bayar</th>
                    <th>Total</th>
                    <th>Biaya</th>
                    <th>Bayar Hutang</th>
                    <th>Sisa Hutang</th>
                </tr>
            </thead>
            <tbody>
                @forelse($transaksiDo as $index => $do)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $do->nomor }}</td>
                        <td>{{ $do->penjual->nama }}</td>
                        <td class="text-right">{{ number_format($do->tonase, 0) }}</td>
                        <td>{{ $do->cara_bayar }}</td>
                        <td class="text-right">{{ number_format($do->sub_total) }}</td>
                        <td class="text-right">{{ number_format($do->biaya_lain + $do->upah_bongkar) }}</td>
                        <td class="text-right">{{ number_format($do->pembayaran_hutang) }}</td>
                        <td class="text-right">{{ number_format($do->sisa_hutang_penjual) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="text-center">Tidak ada transaksi DO</td>
                    </tr>
                @endforelse
                <tr class="bg-light text-bold">
                    <td colspan="3">Total</td>
                    <td class="text-right">{{ number_format($totalTonase, 0) }}</td>
                    <td></td>
                    <td class="text-right">{{ number_format($totalSubTotal) }}</td>
                    <td class="text-right">{{ number_format($totalBiaya) }}</td>
                    <td class="text-right">{{ number_format($totalBayarHutang) }}</td>
                    <td class="text-right">{{ number_format($totalBelumBayar) }}</td>
                </tr>
            </tbody>
        </table>
    </div>

    <!-- Transaksi Operasional -->
    <div class="mt-4">
        <h3>Transaksi Operasional</h3>
        <table>
            <thead>
                <tr>
                    <th>No.</th>
                    <th>Kategori</th>
                    <th>Jenis</th>
                    <th>Nominal</th>
                    <th>Keterangan</th>
                </tr>
            </thead>
            <tbody>
                @forelse($transaksiOperasional as $index => $op)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $op->kategori?->label() ?? '-' }}</td>
                        <td>{{ ucfirst($op->operasional) }}</td>
                        <td class="text-right">{{ number_format($op->nominal) }}</td>
                        <td>{{ $op->keterangan ?: '-' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center">Tidak ada transaksi operasional</td>
                    </tr>
                @endforelse

                <tr class="bg-light text-bold">
                    <td colspan="3">Total Operasional</td>
                    <td class="text-right">{{ number_format($transaksiOperasional->sum('nominal')) }}</td>
                    <td></td>
                </tr>
            </tbody>
        </table>
    </div>

    <!-- Ringkasan -->
    <div class="mt-4 summary">
        <h3>Ringkasan Transaksi DO</h3>
        <table class="summary-table">
            <tr>
                <td width="200">Total Tonase</td>
                <td width="20">:</td>
                <td class="text-right">{{ number_format($totalTonase, 0) }} Kg</td>
            </tr>
            <tr>
                <td>Total Transaksi</td>
                <td>:</td>
                <td class="text-right">{{ number_format($totalSubTotal) }}</td>
            </tr>
            <tr>
                <td>Total Biaya</td>
                <td>:</td>
                <td class="text-right">{{ number_format($totalBiaya) }}</td>
            </tr>
            <tr>
                <td>Total Bayar Hutang</td>
                <td>:</td>
                <td class="text-right">{{ number_format($totalBayarHutang) }}</td>
            </tr>
            <tr>
                <td>Total Belum Bayar</td>
                <td>:</td>
                <td class="text-right">{{ number_format($totalBelumBayar) }}</td>
            </tr>
        </table>
    </div>

    <!-- Ringkasan Operasional -->
    <div class="mt-4 summary">
        <h3>Ringkasan Transaksi Operasional</h3>
        <table class="summary-table">
            <tr>
                <td width="200">Total Operasional</td>
                <td width="20">:</td>
                <td class="text-right">{{ number_format($transaksiOperasional->sum('nominal')) }}</td>
            </tr>
        </table>
    </div>

    <!-- Ringkasan Gabungan -->
    <div class="mt-4 summary">
        <h3>Ringkasan Gabungan DO & Operasional</h3>
        <table class="summary-table">
            <tr>
                <td width="200">Total Pemasukan (DO)</td>
                <td width="20">:</td>
                <td class="text-right">{{ number_format($totalSubTotal) }}</td>
            </tr>
            <tr>
                <td>Total Pengeluaran</td>
                <td>:</td>
                <td class="text-right">{{ number_format($totalBiaya + $transaksiOperasional->sum('nominal')) }}</td>
            </tr>
            <tr class="text-bold">
                <td>Saldo</td>
                <td>:</td>
                <td class="text-right">{{ number_format($totalSubTotal - ($totalBiaya + $transaksiOperasional->sum('nominal'))) }}</td>
            </tr>
            <tr>
                <td>Total Hutang Belum Bayar</td>
                <td>:</td>
                <td class="text-right">{{ number_format($totalBelumBayar) }}</td>
            </tr>
        </table>
    </div>

    <!-- Footer -->
    <div class="mt-4">
        <p class="text-center">
            Dicetak pada {{ now()->isoFormat('dddd, D MMMM Y HH:mm:ss') }}
            oleh {{ auth()->user()->name }}
        </p>
    </div>
</body>

</html>
