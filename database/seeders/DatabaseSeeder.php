<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $this->call(RolesTableSeeder::class);
        $this->call(PermissionsTableSeeder::class);
        $this->call(UserTableSeeder::class);
        $this->call(TahunTableSeeder::class);
        $this->call(KarakterTableSeeder::class);
        $this->call(MateriTableSeeder::class);
        $this->call(SatuanTableSeeder::class);
        $this->call(KelasTableSeeder::class);
        $this->call(DivisiTableSeeder::class);
        $this->call(TanggalTableSeeder::class);
        $this->call(KegiatanTableSeeder::class);
    }
}
