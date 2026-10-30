<?php

namespace Tests\Feature;

use App\Models\Kelompok\Jadwal;
use App\Models\Kelompok\MasterData\Kegiatan;
use App\Models\Kelompok\MasterData\Kelompok;
use App\Models\Kelompok\MasterData\Lokasi;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class KelompokJadwalTest extends TestCase
{
    use DatabaseTransactions;

    protected $user;
    protected $kegiatan;
    protected $kelompok;
    protected $lokasi1;
    protected $lokasi2;

    protected function setUp(): void
    {
        parent::setUp();
        Permission::findOrCreate('master_data');

        $this->user = User::create([
            'username' => 'admin_jadwal_' . uniqid(),
            'nama'     => 'Admin Jadwal Test',
            'email'    => 'admin_jadwal_' . uniqid() . '@example.test',
            'password' => Hash::make('password123'),
            'no_telp'  => '081234567888',
            'status'   => true,
        ]);
        $this->user->givePermissionTo('master_data');

        $this->kelompok = Kelompok::firstOrCreate(
            ['nama' => 'Kelompok Test'],
            ['status' => true, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()]
        );

        $this->kegiatan = Kegiatan::firstOrCreate(
            ['nama' => 'Pengajian Akbar Kelompok'],
            ['lingkup' => 3, 'status' => true, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()]
        );

        $this->lokasi1 = Lokasi::create([
            'nama'        => 'Masjid Al-Ikhlas ' . uniqid(),
            'kelompok_id' => $this->kelompok->id,
            'latitude'    => -6.200000,
            'longitude'   => 106.816666,
            'radius'      => 100,
            'status'      => true,
        ]);

        $this->lokasi2 = Lokasi::create([
            'nama'        => 'Masjid Baiturrahman ' . uniqid(),
            'kelompok_id' => $this->kelompok->id,
            'latitude'    => -6.210000,
            'longitude'   => 106.820000,
            'radius'      => 150,
            'status'      => true,
        ]);
    }

    public function test_unauthenticated_cannot_access_jadwal()
    {
        $response = $this->get('/kelompok/jadwal/index');
        $response->assertRedirect('/login');
    }

    public function test_authenticated_can_access_jadwal_index()
    {
        $response = $this->actingAs($this->user)
            ->get('/kelompok/jadwal/index');

        $response->assertStatus(200);
        $response->assertSee('Jadwal Kegiatan Kelompok');
        $response->assertSee('Tambah Jadwal Kegiatan');
    }

    public function test_can_create_jadwal_total_with_single_lokasi()
    {
        $payload = [
            'kegiatan_id' => $this->kegiatan->id,
            'tipe'        => 'total',
            'lokasi_ids'  => [$this->lokasi1->id],
            'tanggal'     => '2026-11-01',
            'jam_mulai'   => '19:30',
            'jam_selesai' => '21:30',
            'keterangan'  => 'Pengajian rutin terpusat',
            'status'      => 1,
        ];

        $response = $this->actingAs($this->user)
            ->post('/kelompok/jadwal/create', $payload);

        $response->assertStatus(302);

        $this->assertDatabaseHas('kelompok_jadwal', [
            'kegiatan_id' => $this->kegiatan->id,
            'tipe'        => 'total',
            'tanggal'     => '2026-11-01',
            'jam_mulai'   => '19:30:00',
            'jam_selesai' => '21:30:00',
            'status'      => 1,
        ]);

        $jadwal = Jadwal::where('kegiatan_id', $this->kegiatan->id)->latest('id')->first();
        $this->assertNotNull($jadwal);
        $this->assertDatabaseHas('kelompok_jadwal_lokasi', [
            'jadwal_id' => $jadwal->id,
            'lokasi_id' => $this->lokasi1->id,
        ]);
    }

    public function test_can_create_jadwal_turba_with_multi_lokasi()
    {
        $payload = [
            'kegiatan_id' => $this->kegiatan->id,
            'tipe'        => 'turba',
            'lokasi_ids'  => [$this->lokasi1->id, $this->lokasi2->id],
            'tanggal'     => '2026-11-05',
            'jam_mulai'   => '08:00',
            'jam_selesai' => '12:00',
            'keterangan'  => 'Kunjungan turba pengurus',
            'status'      => 1,
        ];

        $response = $this->actingAs($this->user)
            ->post('/kelompok/jadwal/create', $payload);

        $response->assertStatus(302);

        $jadwal = Jadwal::where('tipe', 'turba')->latest('id')->first();
        $this->assertNotNull($jadwal);
        $this->assertEquals(2, $jadwal->getLokasi()->count());

        $this->assertDatabaseHas('kelompok_jadwal_lokasi', [
            'jadwal_id' => $jadwal->id,
            'lokasi_id' => $this->lokasi1->id,
        ]);
        $this->assertDatabaseHas('kelompok_jadwal_lokasi', [
            'jadwal_id' => $jadwal->id,
            'lokasi_id' => $this->lokasi2->id,
        ]);
    }

    public function test_can_update_jadwal_and_sync_multi_lokasi()
    {
        $jadwal = Jadwal::create([
            'kegiatan_id' => $this->kegiatan->id,
            'tipe'        => 'total',
            'tanggal'     => '2026-11-01',
            'jam_mulai'   => '19:30',
            'jam_selesai' => '21:30',
            'status'      => true,
            'created_at'  => Carbon::now(),
            'updated_at'  => Carbon::now(),
        ]);
        $jadwal->getLokasi()->sync([$this->lokasi1->id]);

        $updatePayload = [
            'id'          => $jadwal->id,
            'kegiatan_id' => $this->kegiatan->id,
            'tipe'        => 'turba',
            'lokasi_ids'  => [$this->lokasi2->id], // Diubah hanya ke lokasi2
            'tanggal'     => '2026-11-02',
            'jam_mulai'   => '20:00',
            'jam_selesai' => '22:00',
            'status'      => 1,
        ];

        $response = $this->actingAs($this->user)
            ->post('/kelompok/jadwal/create', $updatePayload);

        $response->assertStatus(302);

        $this->assertDatabaseHas('kelompok_jadwal', [
            'id'        => $jadwal->id,
            'tipe'      => 'turba',
            'tanggal'   => '2026-11-02',
            'jam_mulai' => '20:00:00',
        ]);

        // Lokasi 2 harus ada, Lokasi 1 harus ter-detach
        $this->assertDatabaseHas('kelompok_jadwal_lokasi', [
            'jadwal_id' => $jadwal->id,
            'lokasi_id' => $this->lokasi2->id,
        ]);
        $this->assertDatabaseMissing('kelompok_jadwal_lokasi', [
            'jadwal_id' => $jadwal->id,
            'lokasi_id' => $this->lokasi1->id,
        ]);
    }

    public function test_can_toggle_jadwal_status_via_ajax()
    {
        $jadwal = Jadwal::create([
            'kegiatan_id' => $this->kegiatan->id,
            'tipe'        => 'total',
            'kelompok_id' => $this->kelompok->id,
            'tanggal'     => '2026-11-01',
            'jam_mulai'   => '19:30',
            'jam_selesai' => '21:30',
            'status'      => true,
            'created_at'  => Carbon::now(),
            'updated_at'  => Carbon::now(),
        ]);

        $response = $this->actingAs($this->user)
            ->postJson('/kelompok/jadwal/toggle-status/' . $jadwal->id);

        $response->assertStatus(200);
        $response->assertJson([
            'status'    => 'success',
            'is_active' => false,
        ]);

        $this->assertDatabaseHas('kelompok_jadwal', [
            'id'     => $jadwal->id,
            'status' => 0,
        ]);
    }

    public function test_validation_requires_essential_fields()
    {
        $payload = [
            'kegiatan_id' => '',
            'tipe'        => 'invalid_tipe',
            'lokasi_ids'  => [],
            'tanggal'     => '',
            'jam_mulai'   => '',
            'jam_selesai' => '',
        ];

        $response = $this->actingAs($this->user)
            ->post('/kelompok/jadwal/create', $payload);

        $response->assertSessionHasErrors(['kegiatan_id', 'tipe', 'lokasi_ids', 'tanggal', 'jam_mulai', 'jam_selesai']);
    }
}
