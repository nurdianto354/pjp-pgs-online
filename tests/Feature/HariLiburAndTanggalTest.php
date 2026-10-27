<?php

namespace Tests\Feature;

use App\Models\Aktivitas\HariLibur;
use App\Models\MasterData\Divisi;
use App\Models\MasterData\Tanggal;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class HariLiburAndTanggalTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        Permission::findOrCreate('aktivitas');
        Permission::findOrCreate('master_data');
    }

    protected function createUser(array $permissions = [])
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
        foreach ($permissions as $perm) {
            $user->givePermissionTo($perm);
        }
        return $user;
    }

    /**
     * Test HariLibur duplicate prevention.
     */
    public function test_hari_libur_duplicate_prevention()
    {
        $user = $this->createUser(['aktivitas']);
        $divisi = Divisi::firstOrCreate(
            ['nama' => 'Paud'],
            ['status' => true]
        );

        $testDate = '2025-08-17';
        $timestamp = strtotime($testDate);

        HariLibur::where('divisi_id', $divisi->id)->where('tanggal', $timestamp)->delete();

        // 1st create should succeed
        $response1 = $this->actingAs($user)
            ->post(route('aktivitas.hari_libur.create'), [
                'divisi_id'  => $divisi->id,
                'tanggal'    => $testDate,
                'keterangan' => 'Hari Kemerdekaan',
            ]);
        $response1->assertRedirect();
        $this->assertDatabaseHas('aktivitas_hari_libur', [
            'divisi_id' => $divisi->id,
            'tanggal'   => $timestamp,
            'status'    => 1,
        ]);

        // 2nd create for same divisi and date should be rejected as duplicate
        $response2 = $this->actingAs($user)
            ->post(route('aktivitas.hari_libur.create'), [
                'divisi_id'  => $divisi->id,
                'tanggal'    => $testDate,
                'keterangan' => 'Duplikat Kemerdekaan',
            ]);
        $response2->assertRedirect();

        // Count should still be 1
        $count = HariLibur::where('divisi_id', $divisi->id)->where('tanggal', $timestamp)->count();
        $this->assertEquals(1, $count);
    }

    /**
     * Test Tanggal controller stores dayOfWeek (0-6) instead of dayOfMonth (1-31).
     */
    public function test_tanggal_create_stores_day_of_week()
    {
        $user = $this->createUser(['master_data']);

        // 2026-10-27 is a Tuesday, which is dayOfWeek = 2
        $testDate = '2026-10-27';
        $carbon = Carbon::parse($testDate);
        $timestamp = strtotime($testDate);

        Tanggal::where('tanggal', $timestamp)->delete();

        $response = $this->actingAs($user)
            ->post(route('master_data.tanggal.create'), [
                'tanggal' => $testDate,
            ]);

        $response->assertRedirect();

        $record = Tanggal::where('tanggal', $timestamp)->first();
        $this->assertNotNull($record);
        $this->assertEquals(2, $record->hari, "hari should be dayOfWeek (2 for Tuesday)");
        $this->assertEquals(10, $record->bulan);
        $this->assertEquals(2026, $record->tahun);
    }
}

