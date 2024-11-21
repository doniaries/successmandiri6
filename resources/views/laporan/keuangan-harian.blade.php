<!DOCTYPE html>
<html>

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <style>
        /* Landscape orientation */
        @page {
            size: landscape;
            margin: 1cm;
        }

        /* Reduce font sizes */
        body {
            font-family: Arial, sans-serif;
            line-height: 1.4;
            font-size: 11px;
            /* Ukuran font default lebih kecil */
        }

        .header {
            text-align: center;
            margin-bottom: 15px;
        }

        .header img {
            max-height: 50px;
            margin-bottom: 8px;
        }

        .header h2 {
            font-size: 16px;
            margin: 5px 0;
        }

        .header h3 {
            font-size: 14px;
            margin: 5px 0;
        }

        .header p {
            font-size: 11px;
            margin: 5px 0;
        }

        .header img {
            display: block;
            margin: 0 auto 20px;
            max-width: 200px;
            height: auto;
        }

        .info-section {
            margin-bottom: 15px;
            font-size: 11px;
        }

        .section-title {
            font-size: 12px;
            margin: 15px 0 8px 0;
        }

        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
            font-size: 10px;
        }

        .data-table th,
        .data-table td {
            border: 0.5px solid #ddd;
            padding: 4px 6px;
        }

        .data-table th {
            background: #f5f5f5;
            font-size: 10px;
        }

        .summary-table {
            width: 40%;
            margin-left: auto;
            font-size: 11px;
        }

        .badge {
            padding: 2px 4px;
            border-radius: 3px;
            font-size: 9px;
            display: inline-block;
            min-width: 60px;
            text-align: center;
        }

        .badge-success {
            background: #4CAF50;
            color: white;
        }

        .badge-danger {
            background: #f44336;
            color: white;
        }

        /* Signature section */
        .signature-section {
            margin-top: 4rem;
            page-break-inside: avoid;
        }

        .signature-section p {
            margin: 0.5rem 0;
        }

        .signature-container {
            display: inline-block;
            width: 45%;
            text-align: center;
        }

        .signature-box {
            min-height: 6rem;
            margin: 1rem auto;
            width: 60%;
        }

        /* Float management */
        .clearfix::after {
            content: "";
            clear: both;
            display: table;
        }

        .footer {
            margin-top: 10px;
            font-size: 10px;
            position: fixed;
            bottom: 0;
            width: 100%;
        }

        /* Additional utility classes */
        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        .badge-success {
            background: #4CAF50;
            color: white;
        }

        .badge-danger {
            background: #f44336;
            color: white;
        }
    </style>
</head>

<body>
    <div class="header">
        @if ($perusahaan && $perusahaan->logo)
            <img src="{{ Storage::disk('public')->url($perusahaan->logo) }}" alt="Logo {{ $perusahaan->name }}"
                onerror="this.style.display='none'" />
        @endif
        <h2>{{ optional($perusahaan)->name ?? 'Nama Perusahaan' }}</h2>
        <h3>Laporan Keuangan Harian</h3>
        <p>Periode: {{ Carbon\Carbon::parse($startDate)->format('d/m/Y') }} -
            {{ Carbon\Carbon::parse($endDate)->format('d/m/Y') }}</p>
    </div>

    <!-- Info Section -->
    <div class="info-section">
        <table>
            <tr>
                <td width="100">Dibuat Oleh</td>
                <td width="10">:</td>
                <td>{{ $user->name }}</td>
                <td width="100">Tanggal Cetak</td>
                <td width="10">:</td>
                <td>{{ now()->format('d/m/Y H:i') }}</td>
            </tr>
            <tr>
                <td>Saldo Awal</td>
                <td>:</td>
                <td colspan="4">Rp {{ number_format($saldoAwal, 0, ',', '.') }}</td>
            </tr>
        </table>
    </div>

    <!-- Bagian A: Transaksi Saldo -->
    <h4 class="section-title">A. Tambah Saldo</h4>
    <table class="data-table">
        <thead>
            <tr>
                <th>Tanggal</th>
                <th>Jenis</th>
                <th>Nominal</th>
                <th>Keterangan</th>
            </tr>
        </thead>
        <tbody>
            @forelse($transaksiSaldo as $saldo)
                <tr>
                    <td>{{ Carbon\Carbon::parse($saldo->tanggal)->format('d/m/y H:i') }}</td>
                    <td>{{ $saldo->jenis }}</td>
                    <td class="text-right">{{ number_format($saldo->nominal, 0, ',', '.') }}</td>
                    <td>{{ $saldo->keterangan }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" class="text-center">Tidak ada transaksi saldo</td>
                </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr>
                <td colspan="2" class="text-right"><strong>Total Perubahan Saldo</strong></td>
                <td class="text-right"><strong>{{ number_format($totalPerubahanSaldo, 0, ',', '.') }}</strong></td>
                <td></td>
            </tr>
        </tfoot>
    </table>

    <!-- Bagian B: Transaksi DO -->
    <h4 class="section-title">B. Transaksi DO</h4>
    <table class="data-table">
        <thead>
            <tr>
                <th>Tanggal</th>
                <th>No. DO</th>
                <th>Jenis</th>
                <th>Kategori</th>
                <th>Penjual</th>
                <th>Cara Bayar</th>
                <th class="text-right">Nominal</th>
            </tr>
        </thead>
        <tbody>
            @forelse($transaksiDo as $do)
                <tr>
                    <td>{{ Carbon\Carbon::parse($do->tanggal)->format('d/m/y H:i') }}</td>
                    <td>{{ $do->nomor }}</td>
                    <td>
                        <span class="badge badge-{{ $do->jenis_transaksi === 'Pemasukan' ? 'success' : 'danger' }}">
                            {{ $do->jenis_transaksi }}
                        </span>
                    </td>
                    <td>{{ $do->sub_kategori }}</td>
                    <td>{{ $do->pihak_terkait }}</td>
                    <td>{{ $do->cara_pembayaran }}</td>
                    <td class="text-right">{{ number_format($do->nominal, 0, ',', '.') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="text-center">Tidak ada transaksi DO</td>
                </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr>
                <td colspan="6" class="text-right"><strong>Total Transaksi DO</strong></td>
                <td class="text-right"><strong>{{ number_format($totalTransaksiDo, 0, ',', '.') }}</strong></td>
            </tr>
        </tfoot>
    </table>

    <!-- Bagian C: Transaksi Operasional -->
    <h4 class="section-title">C. Transaksi Operasional</h4>
    <table class="data-table">
        <thead>
            <tr>
                <th>Tanggal</th>
                <th>Jenis</th>
                <th>Kategori</th>
                <th>Pihak</th>
                <th>Keterangan</th>
                <th class="text-right">Nominal</th>
            </tr>
        </thead>
        <tbody>
            @forelse($transaksiOperasional as $operasional)
                <tr>
                    <td>{{ Carbon\Carbon::parse($operasional->tanggal)->format('d/m/y H:i') }}</td>
                    <td>
                        <span
                            class="badge badge-{{ $operasional->jenis_transaksi === 'Pemasukan' ? 'success' : 'danger' }}">
                            {{ ucfirst($operasional->jenis_transaksi) }}
                        </span>
                    </td>
                    <td>{{ $operasional->sub_kategori }}</td>
                    <td>{{ $operasional->pihak_terkait }}</td>
                    <td>{{ $operasional->keterangan }}</td>
                    <td class="text-right">{{ number_format($operasional->nominal, 0, ',', '.') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="text-center">Tidak ada transaksi operasional</td>
                </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr>
                <td colspan="5" class="text-right"><strong>Total Operasional</strong></td>
                <td class="text-right"><strong>{{ number_format($totalOperasional, 0, ',', '.') }}</strong></td>
            </tr>
        </tfoot>
    </table>

    <!-- Bagian D: Ringkasan Kas -->
    <h4 class="section-title">D. Ringkasan Kas</h4>
    <table class="summary-table">
        <tr>
            <td>Saldo Awal</td>
            <td class="text-right">{{ number_format($saldoAwal, 0, ',', '.') }}</td>
        </tr>
        <tr>
            <td>Perubahan Saldo</td>
            <td class="text-right">{{ number_format($totalPerubahanSaldo, 0, ',', '.') }}</td>
        </tr>
        <tr>
            <td>Total Transaksi DO</td>
            <td class="text-right">{{ number_format($totalTransaksiDo, 0, ',', '.') }}</td>
        </tr>
        <tr>
            <td>Total Operasional</td>
            <td class="text-right">{{ number_format($totalOperasional, 0, ',', '.') }}</td>
        </tr>
        <tr>
            <td><strong>Saldo Akhir</strong></td>
            <td class="text-right"><strong>{{ number_format($saldoAkhir, 0, ',', '.') }}</strong></td>
        </tr>
    </table>

    <div class="mt-8 signature-section">
        <table width="100%">
            <tr>
                <!-- Kolom Pimpinan -->
                <td width="50%" class="text-center align-top">
                    <p class="mb-20">Pimpinan</p>
                    <div class="h-24 signature-box"></div>
                    <p class="inline-block px-8 mt-2 font-bold border-t border-black">
                        {{ $perusahaan?->pimpinan ?: '.................................' }}
                    </p>
                </td>

                <!-- Kolom Kasir -->
                <td width="50%" class="text-center align-top">
                    <p class="mb-4">{{ $perusahaan?->kota ?: 'Kamang' }}, {{ now()->translatedFormat('d F Y') }}</p>
                    <p class="mb-20">Kasir</p>
                    <div class="h-24 signature-box"></div>
                    <p class="inline-block px-8 mt-2 font-bold border-t border-black">
                        {{ $user?->name ?: '.................................' }}
                    </p>
                </td>
            </tr>
        </table>
    </div>

    </div>

    <div class="signature-section">
        <table width="100%">
            <tr>
                <td width="100%">
                    <p style="margin-bottom: 5px;">* Seluruh nominal dalam Rupiah</p>
                    <p>Dicetak oleh: {{ $user->name }} pada {{ now()->format('d/m/Y H:i') }}</p>
                </td>
            </tr>
        </table>
    </div>

</body>

</html>
