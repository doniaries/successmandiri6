<?php

namespace Database\Seeders;

use App\Models\Penjual;
use Illuminate\Database\Seeder;

class PenjualSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * Seeds initial penjual data with default null values for non-essential fields
     */
    public function run(): void
    {
        // Define array of penjual data
        $penjuals = [
            [
                'nama' => 'FURQON',
                'alamat' => null,
                'telepon' => null,
                'hutang' => 0
            ],
            [
                'nama' => 'EPI',
                'alamat' => null,
                'telepon' => null,
                'hutang' => 0
            ],
            [
                'nama' => 'ANDES',
                'alamat' => null,
                'telepon' => null,
                'hutang' => 0
            ],
            [
                'nama' => 'LOPON',
                'alamat' => null,
                'telepon' => null,
                'hutang' => 0
            ],
            [
                'nama' => 'HERMAN',
                'alamat' => null,
                'telepon' => null,
                'hutang' => 0
            ],
            [
                'nama' => 'SIJT',
                'alamat' => null,
                'telepon' => null,
                'hutang' => 0
            ],
            [
                'nama' => 'ETI SUSANA',
                'alamat' => null,
                'telepon' => null,
                'hutang' => 1000000
            ],
            [
                'nama' => 'SEBON',
                'alamat' => null,
                'telepon' => null,
                'hutang' => 0
            ],
            [
                'nama' => 'JEKI',
                'alamat' => null,
                'telepon' => null,
                'hutang' => 0
            ],
            [
                'nama' => 'AGUS',
                'alamat' => null,
                'telepon' => null,
                'hutang' => 0
            ],
            [
                'nama' => 'JOKO',
                'alamat' => null,
                'telepon' => null,
                'hutang' => 0
            ],
            [
                'nama' => 'SUKARMIN',
                'alamat' => null,
                'telepon' => null,
                'hutang' => 1000000
            ],
            [
                'nama' => 'DITEG',
                'alamat' => null,
                'telepon' => null,
                'hutang' => 0
            ],
            [
                'nama' => 'KELOMPOK',
                'alamat' => null,
                'telepon' => null,
                'hutang' => 0
            ],
            [
                'nama' => 'UCOK',
                'alamat' => null,
                'telepon' => null,
                'hutang' => 0
            ],
            [
                'nama' => 'ARI WAHYU',
                'alamat' => null,
                'telepon' => null,
                'hutang' => 0
            ],
        ];

        // Loop through and create each penjual record
        foreach ($penjuals as $penjual) {
            Penjual::create($penjual);
        }
    }
}
