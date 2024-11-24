<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>DO #{{ $transaksi->nomor }}</title>
    <style>
        @page {
            size: 165mm 210mm;
            margin: 8mm;
        }

        body {
            font-family: Arial, sans-serif;
            font-size: 11pt;
            line-height: 1.3;
            margin: 0;
            padding: 0;
        }

        /* Header */
        .header {
            text-align: center;
            margin-bottom: 10px;
            padding-bottom: 8px;
            border-bottom: 1px solid #000;
        }

        .company-name {
            font-size: 13pt;
            font-weight: bold;
            margin-bottom: 3px;
        }

        /* Main content */
        .doc-title {
            text-align: center;
            font-weight: bold;
            font-size: 11pt;
            margin: 12px 0;
        }

        /* Table */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        td {
            border: 1px solid #000;
            padding: 5px 8px;
            vertical-align: top;
        }

        .label-col {
            width: 120px;
        }


        /* Footer */
        .footer {
            position: fixed;
            bottom: 8mm;
            left: 0;
            width: 100%;
            text-align: center;
            font-size: 9pt;
        }
    </style>
</head>

<body>
    <div class="header">
        <div class="company-name">CV SUCCESS MANDIRI</div>
        <div>Dusun Sungai Moran Nagari Kamang</div>
        <div>Telp: +62 823-8921-9670</div>
    </div>

    <div class="doc-title">BUKTI TRANSAKSI DO</div>

    <table>
        <tr>
            <td class="label-col">Nomor DO</td>
            <td><strong>{{ $transaksi->nomor }}</strong></td>
        </tr>
        <tr>
            <td>Tanggal & Waktu</td>
            <td>{{ $transaksi->tanggal->format('d/m/Y H:i') }}</td>
        </tr>
        <tr>
            <td>Nama Penjual</td>
            <td>{{ $transaksi->penjual->nama }}</td>
        </tr>
        <tr>
            <td>Nama Supir</td>
            <td>{{ $transaksi->supir }}</td>
        </tr>

        <tr>
            <td>Nomor Polisi</td>
            <td>{{ $transaksi->nomor_polisi }}</td>
        </tr>
        <tr>
            <td>Tonase (Kg)</td>
            <td>{{ number_format($transaksi->tonase, 2) }}</td>
        </tr>
        <tr>
            <td>Harga Satuan</td>
            <td>Rp {{ number_format($transaksi->harga_satuan, 0, ',', '.') }}</td>
        </tr>
        <tr>
            <td>Total</td>
            <td>Rp {{ number_format($transaksi->total, 0, ',', '.') }}</td>
        </tr>
        <tr>
            <td>Upah Bongkar</td>
            <td>Rp {{ number_format($transaksi->upah_bongkar, 0, ',', '.') }}</td>
        </tr>
        <tr>
            <td>Biaya Lain</td>
            <td>Rp {{ number_format($transaksi->biaya_lain, 0, ',', '.') }}</td>
        </tr>
        <tr>
            <td>Hutang Awal</td>
            <td>Rp {{ number_format($transaksi->hutang_awal, 0, ',', '.') }}</td>
        </tr>
        <tr>
            <td>Pembayaran Hutang</td>
            <td>Rp {{ number_format($transaksi->pembayaran_hutang, 0, ',', '.') }}</td>
        </tr>
        <tr>
            <td>Sisa Hutang</td>
            <td><strong>Rp {{ number_format($transaksi->sisa_hutang_penjual, 0, ',', '.') }}</strong></td>
        </tr>
        <tr>
            <td>Cara Bayar</td>
            <td>{{ $transaksi->cara_bayar }}</td>
        </tr>
        <tr>
            <td>Sisa Bayar</td>
            <td><strong>Rp {{ number_format($transaksi->sisa_bayar, 0, ',', '.') }}</strong></td>
        </tr>

    </table>


    <div class="footer">
        Dicetak pada: {{ now()->format('d/m/Y H:i') }} oleh {{ auth()->user()->name }}
    </div>
</body>

</html>
