<?php

namespace Tests\Feature;

use App\Models\Kelompok\Absensi;
use App\Models\Kelompok\Jadwal;
use App\Models\Kelompok\MasterData\Jamaah;
use App\Models\Kelompok\MasterData\Kegiatan;
use App\Models\Kelompok\MasterData\Kelompok;
use App\Models\Kelompok\MasterData\Lokasi;
use App\Models\MasterData\Divisi;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use DatabaseTransactions;

    public function test_authenticated_user_can_access_dashboard()
    {
        Permission::findOrCreate('master_data');

        $user = User::create([
            'username' => 'admin_dash_' . uniqid(),
            'nama'     => 'Admin Dashboard Test',
            'email'    => 'dash_' . uniqid() . '@example.test',
            'password' => Hash::make('password123'),
            'no_telp'  => '081234568888',
            'status'   => true,
        ]);
        $user->givePermissionTo('master_data');

        // Pastikan divisi ada
        Divisi::firstOrCreate(['nama' => 'Remaja'], ['status' => true]);

        $response = $this->actingAs($user)->get('/');

        $response->assertStatus(200);
        $response->assertSee('Selamat Datang');
        $response->assertSee('Total Siswa Generus');
        $response->assertSee('Data Jamaah Kelompok');
        $response->assertSee('Kegiatan', false);
        $response->assertSee('chartDivisiGenerus');
    }
}
