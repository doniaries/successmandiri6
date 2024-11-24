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
            min-height: 1rem;
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
        <img src="{{ asset('images/success.png') }}" alt="Logo {{ $perusahaan->name ?? 'CV SUCCESS MANDIRI' }}"
            style="max-height: 50px; margin-bottom: 8px;" />
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
            </tr>
            <tr>
                <td width="100">Tanggal Cetak</td>
                <td width="10">:</td>
                <td>{{ now()->format('d/m/Y H:i') }}</td>
            </tr>
            <tr>
                <td>Saldo Awal</td>
                <td>:</td>
                <td colspan="4">Rp {{ number_format($saldoAwal ?? 0, 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td>Saldo Terkini</td>
                <td>:</td>
                <td colspan="4" class="text-success">Rp {{ number_format($perusahaan->saldo ?? 0, 0, ',', '.') }}
                </td>
            </tr>
        </table>
    </div>

    {{-- !-- Section A: Transaksi Saldo --> --}}
    @if (isset($transaksiSaldo) && count($transaksiSaldo) > 0)
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
                @foreach ($transaksiSaldo as $saldo)
                    <tr>
                        <td>{{ Carbon\Carbon::parse($saldo->tanggal)->format('d/m/y H:i') }}</td>
                        <td>{{ $saldo->jenis }}</td>
                        <td class="text-right">{{ number_format($saldo->nominal ?? 0, 0, ',', '.') }}</td>
                        <td>{{ $saldo->keterangan }}</td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="2" class="text-right"><strong>Total Perubahan Saldo</strong></td>
                    <td class="text-right">
                        <strong>{{ number_format($totalPerubahanSaldo ?? 0, 0, ',', '.') }}</strong>
                    </td>
                    <td></td>
                </tr>
            </tfoot>
        </table>
    @endif

    <!-- Section B1: Transaksi DO Tunai -->
    @if (isset($transaksiDo))
        <h4 class="section-title">B1. Transaksi DO Tunai</h4>
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
                @php
                    $transaksiTunai = $transaksiDo->where('cara_pembayaran', 'Tunai');
                @endphp
                @forelse($transaksiTunai as $do)
                    <tr>
                        <td>{{ Carbon\Carbon::parse($do->tanggal)->format('d/m/y H:i') }}</td>
                        <td>{{ $do->nomor_referensi }}</td>
                        <td>
                            <span
                                class="badge badge-{{ $do->jenis_transaksi === 'Pemasukan' ? 'success' : 'danger' }}">
                                {{ $do->jenis_transaksi }}
                            </span>
                        </td>
                        <td>{{ $do->sub_kategori }}</td>
                        <td>{{ $do->pihak_terkait }}</td>
                        <td>{{ $do->cara_pembayaran }}</td>
                        <td class="text-right">{{ number_format($do->nominal ?? 0, 0, ',', '.') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center">Tidak ada transaksi DO tunai</td>
                    </tr>
                @endforelse
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="6" class="text-right"><strong>Total Transaksi DO Tunai</strong></td>
                    <td class="text-right">
                        <strong>{{ number_format($transaksiTunai->sum('nominal') ?? 0, 0, ',', '.') }}</strong>
                    </td>
                </tr>
            </tfoot>
        </table>

        <!-- Section B2: Transaksi DO Non Tunai -->
        <h4 class="section-title">B2. Transaksi DO Non Tunai</h4>
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
                @php
                    $transaksiNonTunai = $transaksiDo
                        ->whereIn('cara_pembayaran', ['Transfer', 'cair di luar'])
                        ->whereIn('sub_kategori', ['Pembayaran DO'])
                        ->where('jenis_transaksi', 'Pengeluaran');
                @endphp
                @forelse($transaksiNonTunai as $do)
                    <tr>
                        <td>{{ Carbon\Carbon::parse($do->tanggal)->format('d/m/y H:i') }}</td>
                        <td>{{ $do->nomor_referensi }}</td>
                        <td>
                            <span
                                class="badge badge-{{ $do->jenis_transaksi === 'Pemasukan' ? 'success' : 'danger' }}">
                                {{ $do->jenis_transaksi }}
                            </span>
                        </td>
                        <td>{{ $do->sub_kategori }}</td>
                        <td>{{ $do->pihak_terkait }}</td>
                        <td>{{ $do->cara_pembayaran }}</td>
                        <td class="text-right">{{ number_format($do->nominal ?? 0, 0, ',', '.') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center">Tidak ada transaksi DO non tunai</td>
                    </tr>
                @endforelse
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="6" class="text-right"><strong>Total Transaksi DO Non Tunai</strong></td>
                    <td class="text-right">
                        <strong>{{ number_format($transaksiNonTunai->sum('nominal') ?? 0, 0, ',', '.') }}</strong>
                    </td>
                </tr>
            </tfoot>
        </table>
    @endif

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
    <table class="summary-table" style="border-collapse: collapse; width: 40%;">
        <!-- Saldo Awal -->
        <tr style="border-bottom: 1px solid #ddd;">
            <td style="padding: 8px;"><strong>Saldo Awal</strong></td>
            <td style="text-align: right; padding: 8px;">
                <strong>Rp {{ number_format($saldoAwal, 0, ',', '.') }}</strong>
            </td>
        </tr>

        <!-- Kelompok Pemasukan -->
        <tr>
            <td colspan="2" style="padding: 8px;"><strong>Pemasukan:</strong></td>
        </tr>
        <!-- Upah bongkar dari tabel transaksi DO -->
        <tr>
            <td style="padding: 4px 8px 4px 24px;">Upah Bongkar DO</td>
            <td style="text-align: right; padding: 4px 8px;">
                Rp
                {{ number_format(
                    $transaksiDo->where('sub_kategori', 'Upah Bongkar')->where('jenis_transaksi', 'Pemasukan')->sum('nominal'),
                    0,
                    ',',
                    '.',
                ) }}
            </td>
        </tr>
        <!-- Biaya lain dari tabel transaksi DO -->
        <tr>
            <td style="padding: 4px 8px 4px 24px;">Biaya Lain DO</td>
            <td style="text-align: right; padding: 4px 8px;">
                Rp
                {{ number_format(
                    $transaksiDo->where('sub_kategori', 'Biaya Lain')->where('jenis_transaksi', 'Pemasukan')->sum('nominal'),
                    0,
                    ',',
                    '.',
                ) }}
            </td>
        </tr>
        <!-- Bayar hutang dari tabel transaksi DO -->
        <tr>
            <td style="padding: 4px 8px 4px 24px;">Bayar Hutang DO</td>
            <td style="text-align: right; padding: 4px 8px;">
                Rp
                {{ number_format(
                    $transaksiDo->where('sub_kategori', 'Bayar Hutang')->where('jenis_transaksi', 'Pemasukan')->sum('nominal'),
                    0,
                    ',',
                    '.',
                ) }}
            </td>
        </tr>
        <!-- Operasional dari tabel transaksi operasional -->
        <tr>
            <td style="padding: 4px 8px 4px 24px;">Operasional</td>
            <td style="text-align: right; padding: 4px 8px;">
                Rp
                {{ number_format($transaksiOperasional->where('jenis_transaksi', 'Pemasukan')->sum('nominal'), 0, ',', '.') }}
            </td>
        </tr>
        <!-- Total Pemasukan -->
        <tr style="border-top: 1px solid #ddd; border-bottom: 1px solid #ddd;">
            <td style="padding: 8px;"><strong>Total Pemasukan</strong></td>
            <td style="text-align: right; padding: 8px;">
                <strong class="text-success">Rp {{ number_format($totalPemasukan, 0, ',', '.') }}</strong>
            </td>
        </tr>

        <!-- Kelompok Pengeluaran -->
        <tr>
            <td colspan="2" style="padding: 8px;"><strong>Pengeluaran:</strong></td>
        </tr>
        <!-- Pembayaran DO Tunai -->
        <tr>
            <td style="padding: 4px 8px 4px 24px;">Pembayaran DO (Tunai)</td>
            <td style="text-align: right; padding: 4px 8px;">
                Rp
                {{ number_format(
                    $transaksiDo->where('cara_pembayaran', 'Tunai')->where('jenis_transaksi', 'Pengeluaran')->where('sub_kategori', 'Pembayaran DO')->sum('nominal'),
                    0,
                    ',',
                    '.',
                ) }}
            </td>
        </tr>
        <!-- Pembayaran DO Non-Tunai -->
        <tr>
            <td style="padding: 4px 8px 4px 24px;">Pembayaran DO (Non-Tunai)</td>
            <td style="text-align: right; padding: 4px 8px;">
                Rp
                {{ number_format(
                    $transaksiDo->whereIn('cara_pembayaran', ['Transfer', 'cair di luar'])->where('jenis_transaksi', 'Pengeluaran')->where('sub_kategori', 'Pembayaran DO')->sum('nominal'),
                    0,
                    ',',
                    '.',
                ) }}
            </td>
        </tr>
        <!-- Operasional -->
        <tr>
            <td style="padding: 4px 8px 4px 24px;">Operasional</td>
            <td style="text-align: right; padding: 4px 8px;">
                Rp
                {{ number_format($transaksiOperasional->where('jenis_transaksi', 'Pengeluaran')->sum('nominal'), 0, ',', '.') }}
            </td>
        </tr>
        <!-- Total Pengeluaran -->
        <tr style="border-top: 1px solid #ddd; border-bottom: 1px solid #ddd;">
            <td style="padding: 8px;"><strong>Total Pengeluaran</strong></td>
            <td style="text-align: right; padding: 8px;">
                <strong class="text-danger">Rp {{ number_format($totalPengeluaran, 0, ',', '.') }}</strong>
            </td>
        </tr>

        <!-- Saldo Akhir -->
        <tr style="background-color: #f8f9fa;">
            <td style="padding: 12px;"><strong>Saldo Akhir</strong></td>
            <td style="text-align: right; padding: 12px;">
                <strong>Rp {{ number_format($saldoAkhir, 0, ',', '.') }}</strong>
            </td>
        </tr>
    </table>

    <!-- Tanda Tangan -->
    <table style="width: 100%; margin-top: 20px; font-size: 10px;">
        <tr>
            <td style="width: 60%">
                <p style="margin: 0;">* Catatan:</p>
                <p style="margin: 0;">- Seluruh nominal dalam Rupiah</p>
                <p style="margin: 0;">- Dicetak oleh: {{ $user->name }} pada {{ now()->format('d/m/Y H:i') }}</p>
            </td>
            <td style="width: 20%; text-align: center;">
                <p style="margin-bottom: 40px;">Kasir</p>
                <p style="margin: 0;">(_________________)</p>
                <p style="margin: 0;">Nama Jelas</p>
            </td>
            <td style="width: 20%; text-align: center;">
                <p style="margin-bottom: 40px;">Pimpinan</p>
                <p style="margin: 0;">(_________________)</p>
                <p style="margin: 0; font-style: italic;">{{ $perusahaan->pimpinan }}</p>
            </td>
        </tr>
    </table>

</body>

</html>
