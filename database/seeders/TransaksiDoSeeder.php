<?php

namespace Database\Seeders;

use App\Models\TransaksiDo;
use Illuminate\Database\Seeder;

class TransaksiDoSeeder extends Seeder
{
    public function run()
    {
        $transaksiDos = [
            [
                'nomor' => 'DO-20241203-0001',
                'tanggal' => '2024-12-03 15:22:10',
                'penjual_id' => 1,
                'supir_id' => 2,
                'kendaraan_id' => null,
                'tonase' => 4329.00,
                'harga_satuan' => 3190,
                'sub_total' => 13809510,
                'upah_bongkar' => 0,
                'biaya_lain' => 0,
                'keterangan_biaya_lain' => null,
                'hutang_awal' => 0,
                'pembayaran_hutang' => 0,
                'sisa_hutang_penjual' => 0,
                'sisa_bayar' => 13809510,
                'cara_bayar' => 'Tunai',
            ],
            [
                'nomor' => 'DO-20241203-0002',
                'tanggal' => '2024-12-03 15:22:10',
                'penjual_id' => 2,
                'supir_id' => 3,
                'kendaraan_id' => null,
                'tonase' => 2763.00,
                'harga_satuan' => 3190,
                'sub_total' => 8813970,
                'upah_bongkar' => 0,
                'biaya_lain' => 200000,
                'keterangan_biaya_lain' => null,
                'hutang_awal' => 0,
                'pembayaran_hutang' => 0,
                'sisa_hutang_penjual' => 0,
                'sisa_bayar' => 8613970,
                'cara_bayar' => 'Tunai',
            ],
            [
                'nomor' => 'DO-20241203-0003',
                'tanggal' => '2024-12-03 15:22:10',
                'penjual_id' => 3,
                'supir_id' => 8,
                'kendaraan_id' => null,
                'tonase' => 558.00,
                'harga_satuan' => 3180,
                'sub_total' => 1774440,
                'upah_bongkar' => 0,
                'biaya_lain' => 0,
                'keterangan_biaya_lain' => null,
                'hutang_awal' => 0,
                'pembayaran_hutang' => 0,
                'sisa_hutang_penjual' => 0,
                'sisa_bayar' => 1774440,
                'cara_bayar' => 'Tunai',
            ],
            [
                'nomor' => 'DO-20241203-0004',
                'tanggal' => '2024-12-03 15:22:10',
                'penjual_id' => 4,
                'supir_id' => 5,
                'kendaraan_id' => null,
                'tonase' => 9204.00,
                'harga_satuan' => 3180,
                'sub_total' => 29268720,
                'upah_bongkar' => 0,
                'biaya_lain' => 116000,
                'keterangan_biaya_lain' => null,
                'hutang_awal' => 0,
                'pembayaran_hutang' => 0,
                'sisa_hutang_penjual' => 0,
                'sisa_bayar' => 29152720,
                'cara_bayar' => 'Cair di Luar',
            ],
            [
                'nomor' => 'DO-20241203-0005',
                'tanggal' => '2024-12-03 16:41:45',
                'penjual_id' => 5,
                'supir_id' => null,
                'kendaraan_id' => null,
                'tonase' => 940.00,
                'harga_satuan' => 3180,
                'sub_total' => 2989200,
                'upah_bongkar' => 0,
                'biaya_lain' => 50000,
                'keterangan_biaya_lain' => null,
                'hutang_awal' => 0,
                'pembayaran_hutang' => 0,
                'sisa_hutang_penjual' => 0,
                'sisa_bayar' => 2939200,
                'cara_bayar' => 'Tunai',
            ],
            [
                'nomor' => 'DO-20241203-0006',
                'tanggal' => '2024-12-03 16:43:24',
                'penjual_id' => 6,
                'supir_id' => 7,
                'kendaraan_id' => null,
                'tonase' => 2143.00,
                'harga_satuan' => 3180,
                'sub_total' => 6814740,
                'upah_bongkar' => 0,
                'biaya_lain' => 100000,
                'keterangan_biaya_lain' => null,
                'hutang_awal' => 0,
                'pembayaran_hutang' => 0,
                'sisa_hutang_penjual' => 0,
                'sisa_bayar' => 6714740,
                'cara_bayar' => 'Tunai',
            ],
            [
                'nomor' => 'DO-20241203-0007',
                'tanggal' => '2024-12-03 16:54:23',
                'penjual_id' => 7,
                'supir_id' => 8,
                'kendaraan_id' => null,
                'tonase' => 7896.00,
                'harga_satuan' => 3180,
                'sub_total' => 25109280,
                'upah_bongkar' => 0,
                'biaya_lain' => 400000,
                'keterangan_biaya_lain' => null,
                'hutang_awal' => 1500000,
                'pembayaran_hutang' => 1000000,
                'sisa_hutang_penjual' => 0,
                'sisa_bayar' => 23209280,
                'cara_bayar' => 'Transfer',
            ],
            [
                'nomor' => 'DO-20241203-0008',
                'tanggal' => '2024-12-03 17:06:38',
                'penjual_id' => 8,
                'supir_id' => 9,
                'kendaraan_id' => null,
                'tonase' => 1153.00,
                'harga_satuan' => 3180,
                'sub_total' => 3666540,
                'upah_bongkar' => 0,
                'biaya_lain' => 0,
                'keterangan_biaya_lain' => null,
                'hutang_awal' => 0,
                'pembayaran_hutang' => 0,
                'sisa_hutang_penjual' => 0,
                'sisa_bayar' => 3666540,
                'cara_bayar' => 'Tunai',
            ],
            [
                'nomor' => 'DO-20241203-0009',
                'tanggal' => '2024-12-03 17:08:00',
                'penjual_id' => 9,
                'supir_id' => 10,
                'kendaraan_id' => null,
                'tonase' => 1482.00,
                'harga_satuan' => 3180,
                'sub_total' => 4712760,
                'upah_bongkar' => 0,
                'biaya_lain' => 20000,
                'keterangan_biaya_lain' => null,
                'hutang_awal' => 0,
                'pembayaran_hutang' => 0,
                'sisa_hutang_penjual' => 0,
                'sisa_bayar' => 4692760,
                'cara_bayar' => 'Tunai',
            ],
            [
                'nomor' => 'DO-20241203-0010',
                'tanggal' => '2024-12-03 17:09:07',
                'penjual_id' => 10,
                'supir_id' => 9,
                'kendaraan_id' => null,
                'tonase' => 1384.00,
                'harga_satuan' => 3180,
                'sub_total' => 4401120,
                'upah_bongkar' => 0,
                'biaya_lain' => 15000,
                'keterangan_biaya_lain' => null,
                'hutang_awal' => 0,
                'pembayaran_hutang' => 0,
                'sisa_hutang_penjual' => 0,
                'sisa_bayar' => 4386120,
                'cara_bayar' => 'Tunai',
            ],
            [
                'nomor' => 'DO-20241203-0011',
                'tanggal' => '2024-12-03 17:10:22',
                'penjual_id' => 11,
                'supir_id' => null,
                'kendaraan_id' => null,
                'tonase' => 1482.00,
                'harga_satuan' => 3180,
                'sub_total' => 4712760,
                'upah_bongkar' => 0,
                'biaya_lain' => 20000,
                'keterangan_biaya_lain' => null,
                'hutang_awal' => 0,
                'pembayaran_hutang' => 0,
                'sisa_hutang_penjual' => 0,
                'sisa_bayar' => 4692760,
                'cara_bayar' => 'Tunai',
            ],
            [
                'nomor' => 'DO-20241203-0012',
                'tanggal' => '2024-12-03 17:11:22',
                'penjual_id' => 12,
                'supir_id' => null,
                'kendaraan_id' => null,
                'tonase' => 1153.00,
                'harga_satuan' => 3180,
                'sub_total' => 3666540,
                'upah_bongkar' => 0,
                'biaya_lain' => 0,
                'keterangan_biaya_lain' => null,
                'hutang_awal' => 0,
                'pembayaran_hutang' => 0,
                'sisa_hutang_penjual' => 0,
                'sisa_bayar' => 3666540,
                'cara_bayar' => 'Tunai',
            ],
            [
                'nomor' => 'DO-20241203-0013',
                'tanggal' => '2024-12-03 17:12:22',
                'penjual_id' => 13,
                'supir_id' => null,
                'kendaraan_id' => null,
                'tonase' => 8221.00,
                'harga_satuan' => 3190,
                'sub_total' => 26224990,
                'upah_bongkar' => 0,
                'biaya_lain' => 300000,
                'keterangan_biaya_lain' => null,
                'hutang_awal' => 1500000,
                'pembayaran_hutang' => 1500000,
                'sisa_hutang_penjual' => 0,
                'sisa_bayar' => 24424990,
                'cara_bayar' => 'Transfer',
            ],
            [
                'nomor' => 'DO-20241203-0014',
                'tanggal' => '2024-12-03 17:13:22',
                'penjual_id' => 14,
                'supir_id' => null,
                'kendaraan_id' => null,
                'tonase' => 7637.00,
                'harga_satuan' => 3180,
                'sub_total' => 24285660,
                'upah_bongkar' => 0,
                'biaya_lain' => 80000,
                'keterangan_biaya_lain' => null,
                'hutang_awal' => 0,
                'pembayaran_hutang' => 0,
                'sisa_hutang_penjual' => 0,
                'sisa_bayar' => 24205660,
                'cara_bayar' => 'Transfer',
            ],
            [
                'nomor' => 'DO-20241203-0015',
                'tanggal' => '2024-12-03 17:41:55',
                'penjual_id' => 15,
                'supir_id' => null,
                'kendaraan_id' => null,
                'tonase' => 8447.00,
                'harga_satuan' => 3180,
                'sub_total' => 26861460,
                'upah_bongkar' => 0,
                'biaya_lain' => 650000,
                'keterangan_biaya_lain' => null,
                'hutang_awal' => 0,
                'pembayaran_hutang' => 0,
                'sisa_hutang_penjual' => 0,
                'sisa_bayar' => 26870130,
                'cara_bayar' => 'Tunai',
            ],
            [
                'nomor' => 'DO-20241203-0016',
                'tanggal' => '2024-12-03 17:42:50',
                'penjual_id' => 15,
                'supir_id' => null,
                'kendaraan_id' => null,
                'tonase' => 8042.00,
                'harga_satuan' => 3180,
                'sub_total' => 25573560,
                'upah_bongkar' => 0,
                'biaya_lain' => 650000,
                'keterangan_biaya_lain' => null,
                'hutang_awal' => 0,
                'pembayaran_hutang' => 0,
                'sisa_hutang_penjual' => 0,
                'sisa_bayar' => 25574990,
                'cara_bayar' => 'Tunai',
            ],
            [
                'nomor' => 'DO-20241203-0017',
                'tanggal' => '2024-12-03 17:43:20',
                'penjual_id' => 16,
                'supir_id' => null,
                'kendaraan_id' => null,
                'tonase' => 7637.00,
                'harga_satuan' => 3180,
                'sub_total' => 24285660,
                'upah_bongkar' => 0,
                'biaya_lain' => 80000,
                'keterangan_biaya_lain' => null,
                'hutang_awal' => 0,
                'pembayaran_hutang' => 0,
                'sisa_hutang_penjual' => 0,
                'sisa_bayar' => 24205660,
                'cara_bayar' => 'Transfer',
            ],
        ];

        foreach ($transaksiDos as $transaksiDo) {
            TransaksiDo::create($transaksiDo);
        }
    }
}
