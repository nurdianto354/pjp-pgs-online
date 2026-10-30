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

class KelompokAbsensiTest extends TestCase
{
    use DatabaseTransactions;

    protected $user;
    protected $jadwal;
    protected $lokasiPusat;
    protected $jamaahRemaja;
    protected $jamaahUmum;

    protected function setUp(): void
    {
        parent::setUp();
        Permission::findOrCreate('master_data');

        $this->user = User::create([
            'username' => 'admin_absensi_' . uniqid(),
            'nama'     => 'Admin Absensi Test',
            'email'    => 'admin_abs_' . uniqid() . '@example.test',
            'password' => Hash::make('password123'),
            'no_telp'  => '081234567777',
            'status'   => true,
        ]);
        $this->user->givePermissionTo('master_data');

        $kelompok = Kelompok::firstOrCreate(
            ['nama' => 'Kelompok Test Absensi'],
            ['status' => true, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()]
        );

        $kegiatan = Kegiatan::firstOrCreate(
            ['nama' => 'Pengajian Rutin Test'],
            ['lingkup' => 3, 'status' => true, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()]
        );

        // Lokasi di Monas Jakarta (-6.175392, 106.827153), radius 100 meter
        $this->lokasiPusat = Lokasi::create([
            'nama'        => 'Masjid Monas ' . uniqid(),
            'kelompok_id' => $kelompok->id,
            'latitude'    => -6.175392,
            'longitude'   => 106.827153,
            'radius'      => 100,
            'status'      => true,
        ]);

        $this->jadwal = Jadwal::create([
            'kegiatan_id' => $kegiatan->id,
            'tipe'        => 'total',
            'tanggal'     => Carbon::today()->toDateString(),
            'jam_mulai'   => '19:30',
            'jam_selesai' => '21:30',
            'status'      => true,
            'created_at'  => Carbon::now(),
            'updated_at'  => Carbon::now(),
        ]);
        $this->jadwal->getLokasi()->sync([$this->lokasiPusat->id]);

        $divisiRemaja = Divisi::firstOrCreate(
            ['nama' => 'Remaja'],
            ['status' => true]
        );

        $this->jamaahRemaja = Jamaah::create([
            'nama_lengkap'  => 'Jamaah Remaja ' . uniqid(),
            'jenis_kelamin' => 1,
            'divisi_id'     => $divisiRemaja->id,
            'status'        => true,
        ]);

        // Jamaah umum (tanpa divisi / divisi_id = null)
        $this->jamaahUmum = Jamaah::create([
            'nama_lengkap'  => 'Jamaah Bapak Umum ' . uniqid(),
            'jenis_kelamin' => 1,
            'divisi_id'     => null,
            'status'        => true,
        ]);
    }

    public function test_public_can_access_presensi_form_without_login()
    {
        $response = $this->get('/presensi-kelompok/' . $this->jadwal->id);

        $response->assertStatus(200);
        $response->assertSee('Form Presensi Kegiatan');
        $response->assertSee('Status Kehadiran');
        $response->assertSee('Pilih Jenis Kelamin');
    }

    public function test_public_can_get_jamaah_list_filtered_by_jenis_kelamin()
    {
        $jamaahPerempuan = Jamaah::create([
            'nama_lengkap'  => 'Jamaah Ukhti ' . uniqid(),
            'jenis_kelamin' => 0, // Perempuan
            'divisi_id'     => null,
            'status'        => true,
        ]);

        // 1. Filter khusus laki-laki
        $resLaki = $this->getJson('/presensi-kelompok/' . $this->jadwal->id . '/jamaah?jenis_kelamin=1');
        $resLaki->assertStatus(200);
        $resLaki->assertJsonFragment(['nama_lengkap' => $this->jamaahRemaja->nama_lengkap]);
        $resLaki->assertJsonMissing(['nama_lengkap' => $jamaahPerempuan->nama_lengkap]);

        // 2. Filter khusus perempuan
        $resPerempuan = $this->getJson('/presensi-kelompok/' . $this->jadwal->id . '/jamaah?jenis_kelamin=0');
        $resPerempuan->assertStatus(200);
        $resPerempuan->assertJsonFragment([
            'nama_lengkap' => $jamaahPerempuan->nama_lengkap,
            'divisi'       => 'Kelompok', // Sesuai permintaan: divisi null ditampilkan sebagai "Kelompok"
        ]);
        $resPerempuan->assertJsonMissing(['nama_lengkap' => $this->jamaahRemaja->nama_lengkap]);
    }

    public function test_presensi_hadir_succeeds_when_within_radius()
    {
        // Titik pengguna persis di lokasi Monas (jarak ~0 meter)
        $payload = [
            'jamaah_id' => $this->jamaahRemaja->id,
            'status'    => 'hadir',
            'latitude'  => -6.175392,
            'longitude' => 106.827153,
        ];

        $response = $this->postJson('/presensi-kelompok/' . $this->jadwal->id . '/submit', $payload);

        $response->assertStatus(200);
        $response->assertJson([
            'status' => 'success',
        ]);

        $this->assertDatabaseHas('kelompok_absensi', [
            'jadwal_id'    => $this->jadwal->id,
            'jamaah_id'    => $this->jamaahRemaja->id,
            'status'       => 'hadir',
            'lokasi_id'    => $this->lokasiPusat->id,
            'is_in_radius' => 1,
        ]);
    }

    public function test_presensi_hadir_rejected_when_outside_radius()
    {
        // Titik pengguna jauh (misal Bandung: -6.917464, 107.619122)
        $payload = [
            'jamaah_id' => $this->jamaahRemaja->id,
            'status'    => 'hadir',
            'latitude'  => -6.917464,
            'longitude' => 107.619122,
        ];

        $response = $this->postJson('/presensi-kelompok/' . $this->jadwal->id . '/submit', $payload);

        $response->assertStatus(422);
        $response->assertJson([
            'status' => 'error',
        ]);
        $response->assertSee('Presensi Hadir Ditolak');

        $this->assertDatabaseMissing('kelompok_absensi', [
            'jadwal_id' => $this->jadwal->id,
            'jamaah_id' => $this->jamaahRemaja->id,
        ]);
    }

    public function test_presensi_sdc_succeeds_without_gps_and_requires_keterangan()
    {
        // 1. SDC tanpa keterangan -> harus ditolak
        $payloadTanpaKet = [
            'jamaah_id'  => $this->jamaahUmum->id,
            'status'     => 'sdc',
            'keterangan' => '',
        ];

        $res1 = $this->postJson('/presensi-kelompok/' . $this->jadwal->id . '/submit', $payloadTanpaKet);
        $res1->assertStatus(422);
        $res1->assertJsonFragment(['status' => 'error']);
        $res1->assertSee('WAJIB diisi');

        // 2. SDC dengan keterangan dan TANPA koordinat GPS -> Berhasil! (Bypass GPS)
        $payloadValid = [
            'jamaah_id'  => $this->jamaahUmum->id,
            'status'     => 'sdc',
            'keterangan' => 'Tugas dinas sambung ke luar kota',
            'latitude'   => null,
            'longitude'  => null,
        ];

        $res2 = $this->postJson('/presensi-kelompok/' . $this->jadwal->id . '/submit', $payloadValid);
        $res2->assertStatus(200);
        $res2->assertJson([
            'status' => 'success',
        ]);

        $this->assertDatabaseHas('kelompok_absensi', [
            'jadwal_id'   => $this->jadwal->id,
            'jamaah_id'   => $this->jamaahUmum->id,
            'status'      => 'sdc',
            'keterangan'  => 'Tugas dinas sambung ke luar kota',
            'lokasi_id'   => null,
        ]);
    }

    public function test_presensi_izin_succeeds_without_gps_and_requires_keterangan()
    {
        // 1. Izin tanpa keterangan -> harus ditolak
        $payloadTanpaKet = [
            'jamaah_id'  => $this->jamaahUmum->id,
            'status'     => 'izin',
            'keterangan' => '',
        ];

        $res1 = $this->postJson('/presensi-kelompok/' . $this->jadwal->id . '/submit', $payloadTanpaKet);
        $res1->assertStatus(422);
        $res1->assertJsonFragment(['status' => 'error']);
        $res1->assertSee('WAJIB diisi');

        // 2. Izin dengan keterangan dan TANPA koordinat GPS -> Berhasil! (Bypass GPS)
        $payloadValid = [
            'jamaah_id'  => $this->jamaahUmum->id,
            'status'     => 'izin',
            'keterangan' => 'Sedang demam flu istirahat di rumah',
        ];

        $res2 = $this->postJson('/presensi-kelompok/' . $this->jadwal->id . '/submit', $payloadValid);
        $res2->assertStatus(200);
        $res2->assertJson([
            'status' => 'success',
        ]);

        $this->assertDatabaseHas('kelompok_absensi', [
            'jadwal_id'   => $this->jadwal->id,
            'jamaah_id'   => $this->jamaahUmum->id,
            'status'      => 'izin',
            'keterangan'  => 'Sedang demam flu istirahat di rumah',
        ]);
    }

    public function test_admin_can_access_dashboard_absensi_index_and_detail()
    {
        // Index
        $resIndex = $this->actingAs($this->user)->get('/kelompok/absensi/index');
        $resIndex->assertStatus(200);
        $resIndex->assertSee('Absensi Kegiatan Kelompok');
        $resIndex->assertSee('Rekapitulasi Presensi');

        // Detail
        $resDetail = $this->actingAs($this->user)->get('/kelompok/absensi/detail/' . $this->jadwal->id);
        $resDetail->assertStatus(200);
        $resDetail->assertSee('Detail Rekap Presensi');
    }

    public function test_jamaah_yang_sudah_absen_tidak_ditampilkan_di_dropdown()
    {
        // 1. Sebelum absen, jamaahRemaja muncul di list getJamaah
        $resBefore = $this->getJson('/presensi-kelompok/' . $this->jadwal->id . '/jamaah?jenis_kelamin=1');
        $resBefore->assertStatus(200);
        $resBefore->assertJsonFragment(['nama_lengkap' => $this->jamaahRemaja->nama_lengkap]);

        // 2. Buat presensi untuk jamaahRemaja
        Absensi::create([
            'jadwal_id'      => $this->jadwal->id,
            'jamaah_id'      => $this->jamaahRemaja->id,
            'status'         => 'hadir',
            'waktu_presensi' => Carbon::now(),
        ]);

        // 3. Setelah absen, jamaahRemaja TIDAK BOLEH muncul lagi di dropdown
        $resAfter = $this->getJson('/presensi-kelompok/' . $this->jadwal->id . '/jamaah?jenis_kelamin=1');
        $resAfter->assertStatus(200);
        $resAfter->assertJsonMissing(['nama_lengkap' => $this->jamaahRemaja->nama_lengkap]);
        // Namun jamaahUmum yang belum absen tetap muncul
        $resAfter->assertJsonFragment(['nama_lengkap' => $this->jamaahUmum->nama_lengkap]);
    }

    public function test_public_can_access_rekap_without_login_and_filter_by_jenis_kelamin()
    {
        // Catat presensi 1 laki-laki dan 1 perempuan
        $jamaahPerempuan = Jamaah::create([
            'nama_lengkap'  => 'Ukhti Siti ' . uniqid(),
            'jenis_kelamin' => 0,
            'divisi_id'     => null,
            'status'        => true,
        ]);

        Absensi::create([
            'jadwal_id'      => $this->jadwal->id,
            'jamaah_id'      => $this->jamaahRemaja->id,
            'status'         => 'hadir',
            'waktu_presensi' => Carbon::now(),
        ]);

        Absensi::create([
            'jadwal_id'      => $this->jadwal->id,
            'jamaah_id'      => $jamaahPerempuan->id,
            'status'         => 'sdc',
            'keterangan'     => 'Dinas tugas mengajar',
            'waktu_presensi' => Carbon::now(),
        ]);

        // 1. Akses tanpa login (Semua)
        $resAll = $this->get('/presensi-kelompok/' . $this->jadwal->id . '/rekap');
        $resAll->assertStatus(200);
        $resAll->assertSee('Rekap Kehadiran Kegiatan');
        $resAll->assertSee($this->jamaahRemaja->nama_lengkap);
        $resAll->assertSee($jamaahPerempuan->nama_lengkap);

        // 2. Akses tanpa login difilter Laki-laki (jk=1)
        $resLaki = $this->get('/presensi-kelompok/' . $this->jadwal->id . '/rekap?jk=1');
        $resLaki->assertStatus(200);
        $resLaki->assertSee($this->jamaahRemaja->nama_lengkap);
        $resLaki->assertDontSee($jamaahPerempuan->nama_lengkap);

        // 3. Akses tanpa login difilter Perempuan (jk=0)
        $resPerempuan = $this->get('/presensi-kelompok/' . $this->jadwal->id . '/rekap?jk=0');
        $resPerempuan->assertStatus(200);
        $resPerempuan->assertSee($jamaahPerempuan->nama_lengkap);
        $resPerempuan->assertDontSee($this->jamaahRemaja->nama_lengkap);
    }
}

