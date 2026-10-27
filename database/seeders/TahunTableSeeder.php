<?php

namespace Database\Seeders;

use App\Models\MasterData\Tahun;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class TahunTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Tahun::firstOrCreate(
            ['nama' => '2024/2025'],
            [
                'status'     => true,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]
        );

        Tahun::firstOrCreate(
            ['nama' => '2025/2026'],
            [
                'status'     => true,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]
        );
    }
}

