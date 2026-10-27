<?php

namespace Database\Seeders;

use App\Models\Kelompok\MasterData\Kegiatan;
use App\Models\Kelompok\MasterData\Lokasi;
use Illuminate\Database\Seeder;

class KegiatanTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $kegiatans = [
            [
                'nama'       => 'Penerobosan Pengurus Pusat',
                'lingkup'    => Kegiatan::LINGKUP_PENEROBOSAN_PUSAT,
                'keterangan' => 'Kegiatan penerobosan dan pembinaan langsung dari pengurus pusat',
                'status'     => true,
            ],
            [
                'nama'       => 'Musyawarah & Pembinaan Daerahan',
                'lingkup'    => Kegiatan::LINGKUP_DAERAHAN,
                'keterangan' => 'Kegiatan koordinasi dan musyawarah tingkat daerah',
                'status'     => true,
            ],
            [
                'nama'       => 'Pengajian & Musyawarah Desa',
                'lingkup'    => Kegiatan::LINGKUP_DESA,
                'keterangan' => 'Kegiatan rutin dan pengajian di tingkat desa',
                'status'     => true,
            ],
        ];

        foreach ($kegiatans as $kegiatan) {
            Kegiatan::firstOrCreate(
                ['nama' => $kegiatan['nama'], 'lingkup' => $kegiatan['lingkup']],
                $kegiatan
            );
        }

        $lokasis = [
            [
                'nama'      => 'Pusat Kegiatan PGS',
                'alamat'    => 'Gedung Pusat PGS',
                'latitude'  => -6.17539240,
                'longitude' => 106.82715280,
                'radius'    => 100,
                'status'    => true,
            ],
            [
                'nama'      => 'Aula Daerah',
                'alamat'    => 'Komplek Daerah',
                'latitude'  => -6.20876340,
                'longitude' => 106.84559900,
                'radius'    => 75,
                'status'    => true,
            ],
            [
                'nama'      => 'Masjid Desa',
                'alamat'    => 'Lingkungan Desa',
                'latitude'  => -6.22558800,
                'longitude' => 106.80214400,
                'radius'    => 50,
                'status'    => true,
            ],
        ];

        foreach ($lokasis as $lokasi) {
            Lokasi::updateOrCreate(
                ['nama' => $lokasi['nama']],
                $lokasi
            );
        }
    }
}
