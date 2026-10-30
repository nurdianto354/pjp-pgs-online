<?php

namespace Database\Seeders;

use App\Models\Kelompok\MasterData\Jamaah;
use App\Models\Kelompok\MasterData\Kelompok;
use App\Models\MasterData\Divisi;
use App\Models\Murid\Murid;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class JamaahTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $kelompokPusat = Kelompok::where('nama', 'LIKE', '%Pusat%')->first() ?? Kelompok::first();
        $kelompokBarat = Kelompok::where('nama', 'LIKE', '%Barat%')->first() ?? Kelompok::first();

        $divisiRemajaPranikah = Divisi::whereIn('nama', ['Remaja', 'Pranikah'])->pluck('id')->toArray();
        if (empty($divisiRemajaPranikah)) {
            $divisiRemajaPranikah = [4, 5];
        }

        // Ambil beberapa murid Remaja dan Pranikah
        $muridSample = Murid::whereIn('divisi_id', $divisiRemajaPranikah)
            ->where('status', true)
            ->take(3)
            ->get();

        foreach ($muridSample as $index => $murid) {
            Jamaah::updateOrCreate(
                ['murid_id' => $murid->id],
                [
                    'murid_id'      => $murid->id,
                    'divisi_id'     => $murid->divisi_id,
                    'nama_lengkap'  => $murid->nama_lengkap,
                    'jenis_kelamin' => $murid->jenis_kelamin,
                    'status'        => true,
                    'created_at'    => Carbon::now(),
                    'updated_at'    => Carbon::now(),
                ]
            );
        }

        // Buat contoh jamaah umum non-murid (divisi_id = null)
        $umumSamples = [
            [
                'nama_lengkap'  => 'H. Ahmad Supriyanto',
                'jenis_kelamin' => 1,
                'status'        => true,
            ],
            [
                'nama_lengkap'  => 'Hj. Siti Aminah',
                'jenis_kelamin' => 0,
                'status'        => true,
            ],
        ];

        foreach ($umumSamples as $item) {
            Jamaah::updateOrCreate(
                ['nama_lengkap' => $item['nama_lengkap']],
                [
                    'murid_id'      => null,
                    'divisi_id'     => null,
                    'jenis_kelamin' => $item['jenis_kelamin'],
                    'status'        => $item['status'],
                    'created_at'    => Carbon::now(),
                    'updated_at'    => Carbon::now(),
                ]
            );
        }
    }
}

