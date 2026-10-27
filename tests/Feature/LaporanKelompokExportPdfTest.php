<?php

namespace Tests\Feature;

use App\Models\BimbinganKonseling\LaporanKelompok;
use App\Models\MasterData\Divisi;
use App\Models\MasterData\Kelas;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class LaporanKelompokExportPdfTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        Permission::findOrCreate('bimbingan_konseling');
    }

    public function test_unauthenticated_cannot_export_pdf()
    {
        $response = $this->get('/bimbingan-konseling/laporan-kelompok/export-pdf?tahun=2025&bulan=1');
        $response->assertRedirect('/login');
    }

    public function test_authenticated_user_can_export_pdf_with_dynamic_filename_and_actual_data()
    {
        $user = User::create([
            'username' => 'bk_user_' . uniqid(),
            'nama'     => 'BK Officer Test',
            'email'    => 'bk_' . uniqid() . '@example.test',
            'password' => Hash::make('password123'),
            'no_telp'  => '081234567890',
            'status'   => true,
        ]);
        $user->givePermissionTo('bimbingan_konseling');

        $divisi = Divisi::firstOrCreate(
            ['nama' => 'Divisi Test PDF'],
            ['status' => true]
        );

        $kelas = Kelas::firstOrCreate(
            ['nama' => 'Kelas Test PDF', 'divisi_id' => $divisi->id],
            ['level' => 1, 'status' => true]
        );

        $laporan = LaporanKelompok::create([
            'divisi_id'  => $divisi->id,
            'kelas_id'   => $kelas->id,
            'tahun'      => 2025,
            'bulan'      => 1,
            'kasus'      => 'Kasus Konseling Uji Coba PDF',
            'tanggal'    => strtotime('2025-01-15'),
            'status'     => true,
            'created_at' => time(),
            'updated_at' => time(),
            'created_by' => $user->id,
            'updated_by' => $user->id,
        ]);

        $response = $this->actingAs($user)
            ->get('/bimbingan-konseling/laporan-kelompok/export-pdf?tahun=2025&bulan=1');

        $response->assertStatus(200);
        $response->assertHeader('content-type', 'application/pdf');

        // Verify dynamic filename in Content-Disposition
        $disposition = $response->headers->get('content-disposition');
        $this->assertNotNull($disposition);
        $this->assertStringContainsString('Laporan_BK_Kelompok_2025_Januari.pdf', $disposition);

        // Verify PDF output binary starts with %PDF
        $content = $response->getContent();
        $this->assertStringStartsWith('%PDF', $content);
    }
}

