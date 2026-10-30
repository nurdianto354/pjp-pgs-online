<?php

namespace Tests\Feature;

use App\Models\Kelompok\MasterData\Jamaah;
use App\Models\Kelompok\MasterData\Kelompok;
use App\Models\MasterData\Divisi;
use App\Models\Murid\Murid;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class KelompokJamaahTest extends TestCase
{
    use DatabaseTransactions;

    protected $user;
    protected $kelompok;
    protected $divisiRemaja;

    protected function setUp(): void
    {
        parent::setUp();
        Permission::findOrCreate('master_data');

        $this->user = User::create([
            'username' => 'admin_jamaah_' . uniqid(),
            'nama'     => 'Admin Jamaah Test',
            'email'    => 'admin_jamaah_' . uniqid() . '@example.test',
            'password' => Hash::make('password123'),
            'no_telp'  => '081234567899',
            'status'   => true,
        ]);
        $this->user->givePermissionTo('master_data');

        $this->kelompok = Kelompok::firstOrCreate(
            ['nama' => 'Kelompok Uji Coba'],
            ['status' => true, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()]
        );

        $this->divisiRemaja = Divisi::firstOrCreate(
            ['nama' => 'Remaja'],
            ['status' => true]
        );
    }

    public function test_unauthenticated_cannot_access_jamaah()
    {
        $response = $this->get('/kelompok/master-data/jamaah/index');
        $response->assertRedirect('/login');
    }

    public function test_user_with_permission_can_access_jamaah_index()
    {
        $response = $this->actingAs($this->user)
            ->get('/kelompok/master-data/jamaah/index');

        $response->assertStatus(200);
        $response->assertSee('Data Personal / Jamaah');
        $response->assertSee('Master Data Personal / Jamaah Kelompok');
        $response->assertSee('btnSyncMurid');
    }

    public function test_can_create_jamaah_from_murid()
    {
        $murid = Murid::create([
            'nama_lengkap'  => 'Murid Remaja Test ' . uniqid(),
            'nama_panggilan'=> 'Murid',
            'jenis_kelamin' => 1,
            'divisi_id'     => $this->divisiRemaja->id,
            'kelas_id'      => 1,
            'status'        => true,
            'created_at'    => Carbon::now(),
            'updated_at'    => Carbon::now(),
        ]);

        $payload = [
            'nama_lengkap'  => $murid->nama_lengkap,
            'jenis_kelamin' => 1,
            'murid_id'      => $murid->id,
            'status'        => 1,
        ];

        $response = $this->actingAs($this->user)
            ->post('/kelompok/master-data/jamaah/create', $payload);

        $response->assertStatus(302);

        $this->assertDatabaseHas('kelompok_m_jamaah', [
            'nama_lengkap' => $murid->nama_lengkap,
            'jenis_kelamin'=> 1,
            'murid_id'     => $murid->id,
            'divisi_id'    => $this->divisiRemaja->id,
            'status'       => 1,
        ]);
    }

    public function test_can_create_manual_jamaah_with_empty_divisi()
    {
        $payload = [
            'nama_lengkap'  => 'Jamaah Umum Non Murid ' . uniqid(),
            'jenis_kelamin' => 0, // Perempuan
            'murid_id'      => '', // Manual (kosong)
            'status'        => 1,
        ];

        $response = $this->actingAs($this->user)
            ->post('/kelompok/master-data/jamaah/create', $payload);

        $response->assertStatus(302);

        $this->assertDatabaseHas('kelompok_m_jamaah', [
            'nama_lengkap' => $payload['nama_lengkap'],
            'jenis_kelamin'=> 0,
            'murid_id'     => null,
            'divisi_id'    => null, // Sesuai syarat: divisi_id empty (null) jika tidak dari murid
            'status'       => 1,
        ]);
    }

    public function test_can_update_existing_jamaah()
    {
        $jamaah = Jamaah::create([
            'nama_lengkap'  => 'Nama Lama ' . uniqid(),
            'jenis_kelamin' => 1,
            'murid_id'      => null,
            'divisi_id'     => null,
            'status'        => true,
            'created_at'    => Carbon::now(),
            'updated_at'    => Carbon::now(),
        ]);

        $updatePayload = [
            'id'            => $jamaah->id,
            'nama_lengkap'  => 'Nama Baru Terupdate',
            'jenis_kelamin' => 0,
            'murid_id'      => '',
            'status'        => 1,
        ];

        $response = $this->actingAs($this->user)
            ->post('/kelompok/master-data/jamaah/create', $updatePayload);

        $response->assertStatus(302);

        $this->assertDatabaseHas('kelompok_m_jamaah', [
            'id'           => $jamaah->id,
            'nama_lengkap' => 'Nama Baru Terupdate',
            'jenis_kelamin'=> 0,
            'divisi_id'    => null,
        ]);
    }

    public function test_can_toggle_jamaah_status_via_ajax()
    {
        $jamaah = Jamaah::create([
            'nama_lengkap'  => 'Jamaah Toggle ' . uniqid(),
            'jenis_kelamin' => 1,
            'murid_id'      => null,
            'divisi_id'     => null,
            'status'        => true,
            'created_at'    => Carbon::now(),
            'updated_at'    => Carbon::now(),
        ]);

        $response = $this->actingAs($this->user)
            ->postJson('/kelompok/master-data/jamaah/toggle-status/' . $jamaah->id);

        $response->assertStatus(200);
        $response->assertJson([
            'status'    => 'success',
            'is_active' => false,
        ]);

        $this->assertDatabaseHas('kelompok_m_jamaah', [
            'id'     => $jamaah->id,
            'status' => 0,
        ]);

        // Toggle kedua: aktifkan kembali
        $response2 = $this->actingAs($this->user)
            ->postJson('/kelompok/master-data/jamaah/toggle-status/' . $jamaah->id);

        $response2->assertStatus(200);
        $response2->assertJson([
            'status'    => 'success',
            'is_active' => true,
        ]);

        $this->assertDatabaseHas('kelompok_m_jamaah', [
            'id'     => $jamaah->id,
            'status' => 1,
        ]);
    }

    public function test_validation_requires_nama_lengkap_and_jenis_kelamin()
    {
        $payload = [
            'nama_lengkap'  => '',
            'jenis_kelamin' => '',
        ];

        $response = $this->actingAs($this->user)
            ->post('/kelompok/master-data/jamaah/create', $payload);

        $response->assertSessionHasErrors(['nama_lengkap', 'jenis_kelamin']);
    }

    public function test_sync_inserts_new_murid_and_updates_existing_murid()
    {
        // 1. Murid baru (belum ada di tabel jamaah)
        $muridBaru = Murid::create([
            'nama_lengkap'   => 'Murid Baru Remaja ' . uniqid(),
            'nama_panggilan' => 'Baru',
            'jenis_kelamin'  => 1,
            'divisi_id'      => $this->divisiRemaja->id,
            'kelas_id'       => 1,
            'status'         => true,
            'created_at'     => Carbon::now(),
            'updated_at'     => Carbon::now(),
        ]);

        // 2. Murid yang sudah pernah ada di tabel jamaah
        $muridLama = Murid::create([
            'nama_lengkap'   => 'Murid Lama Awal ' . uniqid(),
            'nama_panggilan' => 'Lama',
            'jenis_kelamin'  => 0,
            'divisi_id'      => $this->divisiRemaja->id,
            'kelas_id'       => 1,
            'status'         => true,
            'created_at'     => Carbon::now(),
            'updated_at'     => Carbon::now(),
        ]);

        $jamaahEksis = Jamaah::create([
            'nama_lengkap'  => $muridLama->nama_lengkap,
            'jenis_kelamin' => 0,
            'murid_id'      => $muridLama->id,
            'divisi_id'     => $this->divisiRemaja->id,
            'status'        => true,
            'created_at'    => Carbon::now(),
            'updated_at'    => Carbon::now(),
        ]);

        // Perbarui nama murid di master murid
        $muridLama->nama_lengkap = 'Murid Lama Nama Baru ' . uniqid();
        $muridLama->save();

        // Panggil endpoint sync
        $response = $this->actingAs($this->user)
            ->postJson('/kelompok/master-data/jamaah/sync');

        $response->assertStatus(200);
        $response->assertJson([
            'status' => 'success',
        ]);

        // Pastikan murid baru masuk ke jamaah
        $this->assertDatabaseHas('kelompok_m_jamaah', [
            'murid_id'     => $muridBaru->id,
            'nama_lengkap' => $muridBaru->nama_lengkap,
            'divisi_id'    => $this->divisiRemaja->id,
            'jenis_kelamin'=> 1,
        ]);

        // Pastikan murid lama terupdate namanya
        $this->assertDatabaseHas('kelompok_m_jamaah', [
            'id'           => $jamaahEksis->id,
            'murid_id'     => $muridLama->id,
            'nama_lengkap' => $muridLama->nama_lengkap,
        ]);
    }

    public function test_sync_ignores_murid_other_than_remaja_and_pranikah()
    {
        $divisiPaud = Divisi::firstOrCreate(
            ['nama' => 'Paud'],
            ['status' => true]
        );

        $muridPaud = Murid::create([
            'nama_lengkap'   => 'Anak Paud Test ' . uniqid(),
            'nama_panggilan' => 'Paud',
            'jenis_kelamin'  => 1,
            'divisi_id'      => $divisiPaud->id,
            'kelas_id'       => 1,
            'status'         => true,
            'created_at'     => Carbon::now(),
            'updated_at'     => Carbon::now(),
        ]);

        $response = $this->actingAs($this->user)
            ->postJson('/kelompok/master-data/jamaah/sync');

        $response->assertStatus(200);

        // Murid Paud tidak boleh masuk ke kelompok_m_jamaah
        $this->assertDatabaseMissing('kelompok_m_jamaah', [
            'murid_id' => $muridPaud->id,
        ]);
    }
}

