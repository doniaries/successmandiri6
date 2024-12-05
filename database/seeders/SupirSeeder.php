<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Supir;

class SupirSeeder extends Seeder
{
    public function run(): void
    {
        $supirs = [
            [
                'nama' => 'ICAN',
                'telepon' => 'null',
                'alamat' => 'null',
                'hutang' => 0,
                'riwayat_bayar' => 'null',
            ],
            [
                'nama' => 'SIIT',
                'telepon' => 'null',
                'alamat' => 'null',
                'hutang' => 0,
                'riwayat_bayar' => 'null',
            ],
            [
                'nama' => 'NARO',
                'telepon' => 'null',
                'alamat' => 'null',
                'hutang' => 0,
                'riwayat_bayar' => 'null',
            ],
            [
                'nama' => 'AGUS',
                'telepon' => 'null',
                'alamat' => 'null',
                'hutang' => 0,
                'riwayat_bayar' => 'null',
            ],
            [
                'nama' => 'WILCO',
                'telepon' => 'null',
                'alamat' => 'null',
                'hutang' => 0,
                'riwayat_bayar' => 'null',
            ],
            [
                'nama' => 'KOMBET',
                'telepon' => 'null',
                'alamat' => 'null',
                'hutang' => 0,
                'riwayat_bayar' => 'null',
            ],
            [
                'nama' => 'DODY',
                'telepon' => 'null',
                'alamat' => 'null',
                'hutang' => 0,
                'riwayat_bayar' => 'null',
            ],
            [
                'nama' => 'AGUNG',
                'telepon' => 'null',
                'alamat' => 'null',
                'hutang' => 0,
                'riwayat_bayar' => 'null',
            ],
            [
                'nama' => 'EKO',
                'telepon' => 'null',
                'alamat' => 'null',
                'hutang' => 0,
                'riwayat_bayar' => 'null',
            ],
            // Add more test drivers as needed
        ];

        foreach ($supirs as $supir) {
            Supir::create($supir);
        }
    }
}
