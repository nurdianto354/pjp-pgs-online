<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class PermissionsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $permissions = [
            'absensi',
            'aktivitas',
            'bimbingan_konseling',
            'bimbingan_konseling.laporan_daerah',
            'bimbingan_konseling.laporan_desa',
            'kegiatan',
            'kurikulum_target',
            'laporan',
            'master_data',
            'master_user',
            'master_user.permission',
            'master_user.role',
            'master_user.user',
            'murid',
            'pencapaian_target',
            'rekap_absensi',
        ];

        foreach ($permissions as $perm) {
            Permission::firstOrCreate(['name' => $perm]);
        }
    }
}
