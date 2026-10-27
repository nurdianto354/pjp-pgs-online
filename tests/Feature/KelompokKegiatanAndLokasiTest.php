<?php

namespace Tests\Feature;

use App\Models\Kelompok\MasterData\Kegiatan;
use App\Models\Kelompok\MasterData\Lokasi;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class KelompokKegiatanAndLokasiTest extends TestCase
{
    use DatabaseTransactions;

    protected $user;

    protected function setUp(): void
    {
        parent::setUp();
        Permission::findOrCreate('master_data');

        $this->user = User::create([
            'username' => 'admin_kelompok_' . uniqid(),
            'nama'     => 'Admin Master Kelompok',
            'email'    => 'admin_' . uniqid() . '@example.test',
            'password' => Hash::make('password123'),
            'no_telp'  => '081234567890',
            'status'   => true,
        ]);
        $this->user->givePermissionTo('master_data');
    }

    public function test_unauthenticated_cannot_access_kegiatan_or_lokasi()
    {
        $response1 = $this->get('/kelompok/master-data/kegiatan/index');
        $response1->assertRedirect('/login');

        $response2 = $this->get('/kelompok/master-data/lokasi/index');
        $response2->assertRedirect('/login');
    }

    public function test_kegiatan_index_renders_with_dummy_data()
    {
        $response = $this->actingAs($this->user)
            ->get('/kelompok/master-data/kegiatan/index');

        $response->assertStatus(200);
        $response->assertSee('Data Kegiatan');
        $response->assertSee('Penerobosan Pengurus Pusat');
        $response->assertSee('Musyawarah &amp; Pembinaan Daerahan', false);
        $response->assertSee('Pengajian &amp; Musyawarah Desa', false);
    }

    public function test_can_create_new_kegiatan()
    {
        $payload = [
            'nama'       => 'Penerobosan Khusus Pusat',
            'lingkup'    => 1, // Penerobosan Pusat
            'keterangan' => 'Keterangan uji coba',
            'status'     => 1,
        ];

        $response = $this->actingAs($this->user)
            ->post('/kelompok/master-data/kegiatan/create', $payload);

        $response->assertStatus(302);
        $this->assertDatabaseHas('kelompok_m_kegiatan', [
            'nama'    => 'Penerobosan Khusus Pusat',
            'lingkup' => 1,
            'status'  => 1,
        ]);
    }

    public function test_can_update_existing_kegiatan()
    {
        $kegiatan = Kegiatan::create([
            'nama'       => 'Kegiatan Lama',
            'lingkup'    => 2,
            'keterangan' => 'Sebelum update',
            'status'     => 1,
        ]);

        $payload = [
            'id'         => $kegiatan->id,
            'nama'       => 'Kegiatan Baru Diedit',
            'lingkup'    => 3,
            'keterangan' => 'Sesudah update',
            'status'     => 1,
        ];

        $response = $this->actingAs($this->user)
            ->post('/kelompok/master-data/kegiatan/create', $payload);

        $response->assertStatus(302);
        $this->assertDatabaseHas('kelompok_m_kegiatan', [
            'id'      => $kegiatan->id,
            'nama'    => 'Kegiatan Baru Diedit',
            'lingkup' => 3,
        ]);
    }

    public function test_toggle_status_kegiatan_without_deleting_record()
    {
        $kegiatan = Kegiatan::create([
            'nama'    => 'Kegiatan Toggle Test',
            'lingkup' => 2,
            'status'  => true,
        ]);

        // Toggle from Active to Non Active
        $response = $this->actingAs($this->user)
            ->postJson('/kelompok/master-data/kegiatan/toggle-status/' . $kegiatan->id);

        $response->assertStatus(200);
        $response->assertJson([
            'status'    => 'success',
            'is_active' => false,
        ]);

        $kegiatan->refresh();
        $this->assertEquals(0, $kegiatan->status);
        $this->assertDatabaseHas('kelompok_m_kegiatan', ['id' => $kegiatan->id]);

        // Toggle back to Active
        $response2 = $this->actingAs($this->user)
            ->postJson('/kelompok/master-data/kegiatan/toggle-status/' . $kegiatan->id);

        $response2->assertStatus(200);
        $response2->assertJson([
            'status'    => 'success',
            'is_active' => true,
        ]);

        $kegiatan->refresh();
        $this->assertEquals(1, $kegiatan->status);
        $this->assertDatabaseHas('kelompok_m_kegiatan', ['id' => $kegiatan->id]);
    }

    public function test_lokasi_crud_with_coordinates_and_toggle_status()
    {
        // 1. Create Lokasi with Coordinates & Radius
        $response = $this->actingAs($this->user)
            ->post('/kelompok/master-data/lokasi/create', [
                'nama'      => 'Masjid Al-Barkah Geofence',
                'alamat'    => 'Jl. Raya Kebon Jeruk No. 45',
                'latitude'  => -6.18952000,
                'longitude' => 106.77254000,
                'radius'    => 80,
                'status'    => 1,
            ]);

        $response->assertStatus(302);
        $this->assertDatabaseHas('kelompok_m_lokasi', [
            'nama'      => 'Masjid Al-Barkah Geofence',
            'latitude'  => -6.18952000,
            'longitude' => 106.77254000,
            'radius'    => 80,
            'status'    => 1,
        ]);

        $lokasi = Lokasi::where('nama', 'Masjid Al-Barkah Geofence')->first();

        // 2. View Lokasi index and verify coordinates and radius rendered
        $viewResponse = $this->actingAs($this->user)
            ->get('/kelompok/master-data/lokasi/index');
        $viewResponse->assertStatus(200);
        $viewResponse->assertSee('Masjid Al-Barkah Geofence');
        $viewResponse->assertSee('-6.18952000');
        $viewResponse->assertSee('Radius: 80m');

        // 3. Toggle Status Lokasi
        $toggleResponse = $this->actingAs($this->user)
            ->postJson('/kelompok/master-data/lokasi/toggle-status/' . $lokasi->id);

        $toggleResponse->assertStatus(200);
        $toggleResponse->assertJson([
            'status'    => 'success',
            'is_active' => false,
        ]);

        $lokasi->refresh();
        $this->assertEquals(0, $lokasi->status);
        $this->assertDatabaseHas('kelompok_m_lokasi', ['id' => $lokasi->id]);
    }
}
