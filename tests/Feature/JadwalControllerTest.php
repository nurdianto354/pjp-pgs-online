<?php

namespace Tests\Feature;

use App\Models\Aktivitas\Jadwal;
use App\Models\MasterData\Divisi;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class JadwalControllerTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        Permission::findOrCreate('aktivitas');
    }

    protected function createUser()
    {
        $unique = uniqid();
        $user = User::create([
            'username' => 'user_' . $unique,
            'nama'     => 'User ' . $unique,
            'email'    => 'user_' . $unique . '@example.test',
            'password' => Hash::make('secret_default_123'),
            'no_telp'  => '081234567890',
            'status'   => true,
        ]);
        $user->givePermissionTo('aktivitas');
        return $user;
    }

    /**
     * Test creating a new Jadwal when it does not exist yet.
     */
    public function test_create_new_jadwal_successfully()
    {
        $user = $this->createUser();
        $divisi = Divisi::firstOrCreate(
            ['nama' => 'Paud'],
            ['status' => true]
        );

        // Delete any existing jadwal for Paud on hari 5 (Friday) to test fresh creation
        Jadwal::where('divisi_id', $divisi->id)->where('hari', 5)->delete();

        $response = $this->actingAs($user)
            ->post(route('aktivitas.jadwal.create'), [
                'divisi_id'     => $divisi->id,
                'hari'          => 5,
                'waktu_mulai'   => '08:00',
                'waktu_selesai' => '10:00',
            ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('aktivitas_jadwal', [
            'divisi_id'     => $divisi->id,
            'hari'          => 5,
            'waktu_mulai'   => '08:00',
            'waktu_selesai' => '10:00',
            'status'        => 1,
        ]);
    }

    /**
     * Test duplicate active Jadwal is prevented.
     */
    public function test_duplicate_jadwal_is_prevented()
    {
        $user = $this->createUser();
        $divisi = Divisi::firstOrCreate(
            ['nama' => 'Paud'],
            ['status' => true]
        );

        Jadwal::where('divisi_id', $divisi->id)->where('hari', 5)->delete();

        // Create initial
        Jadwal::create([
            'divisi_id'     => $divisi->id,
            'hari'          => 5,
            'waktu_mulai'   => '08:00',
            'waktu_selesai' => '10:00',
            'status'        => true,
        ]);

        // Attempt duplicate
        $response = $this->actingAs($user)
            ->post(route('aktivitas.jadwal.create'), [
                'divisi_id'     => $divisi->id,
                'hari'          => 5,
                'waktu_mulai'   => '09:00',
                'waktu_selesai' => '11:00',
            ]);

        $response->assertRedirect();
        // The original timing should still be preserved
        $this->assertDatabaseHas('aktivitas_jadwal', [
            'divisi_id'     => $divisi->id,
            'hari'          => 5,
            'waktu_mulai'   => '08:00',
        ]);
    }
}

