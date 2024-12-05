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
                'nomor' => 'DO-' . now()->format('Ymd') . '-0001',
                'tanggal' => now(),
                'penjual_id' => 1, // FURQON
                'supir_id' => 1, // FURQON
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
                'nomor' => 'DO-' . now()->format('Ymd') . '-0002',
                'tanggal' => now(),
                'penjual_id' => 2, // EPI
                'supir_id' => null,
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
                'nomor' => 'DO-' . now()->format('Ymd') . '-0003',
                'tanggal' => now(),
                'penjual_id' => 3, // ANDES
                'supir_id' => null,
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
                'nomor' => 'DO-' . now()->format('Ymd') . '-0004',
                'tanggal' => now(),
                'penjual_id' => 4, // LOPON
                'supir_id' => 2, // ICAN
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
                'nomor' => 'DO-' . now()->format('Ymd') . '-0005',
                'tanggal' => now(),
                'penjual_id' => 5, // HERMAN
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
                'nomor' => 'DO-' . now()->format('Ymd') . '-0006',
                'tanggal' => now(),
                'penjual_id' => 6, // SIIT
                'supir_id' => 3, // SIIT
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
                'nomor' => 'DO-' . now()->format('Ymd') . '-0007',
                'tanggal' => now(),
                'penjual_id' => 7, // ETI SUSANA
                'supir_id' => 4, // NARO
                'kendaraan_id' => null,
                'tonase' => 7896.00,
                'harga_satuan' => 3180,
                'sub_total' => 25109280,
                'upah_bongkar' => 0,
                'biaya_lain' => 400000,
                'keterangan_biaya_lain' => null,
                'hutang_awal' => 1000000,
                'pembayaran_hutang' => 1000000,
                'sisa_hutang_penjual' => 0,
                'sisa_bayar' => 23709280,
                'cara_bayar' => 'Transfer',
            ],
            [
                'nomor' => 'DO-' . now()->format('Ymd') . '-0008',
                'tanggal' => now(),
                'penjual_id' => 14, // KELOMPOK
                'supir_id' => null,
                'kendaraan_id' => null,
                'tonase' => 8221.00,
                'harga_satuan' => 3190,
                'sub_total' => 26224990,
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
                'nomor' => 'DO-' . now()->format('Ymd') . '-0009',
                'tanggal' => now(),
                'penjual_id' => 16, // ARI WAHYU
                'supir_id' => 5, // EKO
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
