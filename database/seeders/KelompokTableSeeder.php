<?php

namespace Database\Seeders;

use App\Models\Kelompok\MasterData\Kelompok;
use Illuminate\Database\Seeder;

class KelompokTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $kelompoks = [
            ['nama' => 'Kelompok Pusat', 'status' => true],
            ['nama' => 'Kelompok Barat', 'status' => true],
            ['nama' => 'Kelompok Timur', 'status' => true],
            ['nama' => 'Kelompok Utara', 'status' => true],
            ['nama' => 'Kelompok Selatan', 'status' => true],
        ];

        foreach ($kelompoks as $k) {
            Kelompok::firstOrCreate(['nama' => $k['nama']], $k);
        }
    }
}

