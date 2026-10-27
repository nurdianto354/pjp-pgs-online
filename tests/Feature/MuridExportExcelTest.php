<?php

namespace Tests\Feature;

use App\Models\MasterData\Divisi;
use App\Models\MasterData\Kelas;
use App\Models\Murid\Murid;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx as XlsxReader;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class MuridExportExcelTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        Permission::findOrCreate('murid');
        Role::findOrCreate('paud');
        Role::findOrCreate('admin');
    }

    protected function createUser(array $attributes = [])
    {
        $unique = uniqid();
        return User::create(array_merge([
            'username' => 'user_' . $unique,
            'nama'     => 'User ' . $unique,
            'email'    => 'user_' . $unique . '@example.test',
            'password' => Hash::make('secret_default_123'),
            'no_telp'  => '081234567890',
            'status'   => true,
        ], $attributes));
    }

    /**
     * Test 1: User dengan role divisi (misal: 'paud') dapat mengekspor Excel tanpa crash (500).
     */
    public function test_division_role_user_can_export_excel_without_crashing()
    {
        $divisiPaud = Divisi::firstOrCreate(
            ['nama' => 'Paud'],
            ['status' => true]
        );

        $kelas = Kelas::firstOrCreate(
            ['nama' => 'Kelas Paud 1', 'divisi_id' => $divisiPaud->id],
            ['status' => true]
        );

        Murid::create([
            'nama_lengkap'   => 'Budi Santoso',
            'nama_panggilan' => 'Budi',
            'jenis_kelamin'  => 1,
            'divisi_id'      => $divisiPaud->id,
            'kelas_id'       => $kelas->id,
            'tempat_lahir'   => 'Surabaya',
            'tanggal_lahir'  => '2020-01-01',
            'alamat'         => 'Jl. Test No. 1',
            'no_telp'        => '0812345678',
            'status'         => true,
        ]);

        $user = $this->createUser();
        $user->givePermissionTo('murid');
        $user->assignRole('paud');

        $response = $this->actingAs($user)
            ->get(route('murid.export_excel'));

        $response->assertStatus(200);
        $this->assertEquals(
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            $response->headers->get('Content-Type')
        );
        $this->assertStringContainsString('Data Murid.xlsx', $response->headers->get('Content-Disposition'));
    }

    /**
     * Test 2: Verifikasi bahwa header Excel memiliki 'Tempat Lahir' dan 'Tanggal Lahir'.
     */
    public function test_excel_headers_have_correct_tempat_lahir_and_tanggal_lahir()
    {
        $divisiPaud = Divisi::firstOrCreate(
            ['nama' => 'Paud'],
            ['status' => true]
        );

        $user = $this->createUser();
        $user->givePermissionTo('murid');
        $user->assignRole('paud');

        $response = $this->actingAs($user)
            ->get(route('murid.export_excel'));

        $response->assertStatus(200);

        $content = $response->streamedContent();
        $tempPath = tempnam(sys_get_temp_dir(), 'test_murid_') . '.xlsx';
        file_put_contents($tempPath, $content);

        try {
            $reader = new XlsxReader();
            $spreadsheet = $reader->load($tempPath);
            $sheet = $spreadsheet->getActiveSheet();

            // Row 1 contains headers:
            // Col 1 (A): No.
            // Col 2 (B): Nama Lengkap
            // Col 3 (C): Nama Panggilan
            // Col 4 (D): Jenis Kelamin
            // Col 5 (E): Kelas
            // Col 6 (F): Tempat Lahir
            // Col 7 (G): Tanggal Lahir
            // Col 8 (H): Alamat
            // Col 9 (I): No. Telp
            $this->assertEquals('Tempat Lahir', $sheet->getCell('F1')->getValue());
            $this->assertEquals('Tanggal Lahir', $sheet->getCell('G1')->getValue());
        } finally {
            if (file_exists($tempPath)) {
                @unlink($tempPath);
            }
        }
    }

    /**
     * Test 3: Null-safety dan integritas rendering data murid ke spreadsheet.
     */
    public function test_export_excel_with_murid_data_renders_kelas_properly()
    {
        $divisiPaud = Divisi::firstOrCreate(
            ['nama' => 'Paud'],
            ['status' => true]
        );

        $user = $this->createUser();
        $user->givePermissionTo('murid');
        $user->assignRole('paud');

        $response = $this->actingAs($user)
            ->get(route('murid.export_excel'));

        $response->assertStatus(200);

        $content = $response->streamedContent();
        $tempPath = tempnam(sys_get_temp_dir(), 'test_murid_') . '.xlsx';
        file_put_contents($tempPath, $content);

        try {
            $reader = new XlsxReader();
            $spreadsheet = $reader->load($tempPath);
            $sheet = $spreadsheet->getActiveSheet();

            // Verifikasi baris data (Row 2):
            // Pastikan tidak ada cell bernilai null error pada kolom Kelas (E)
            $kelasValue = $sheet->getCell('E2')->getValue();
            $this->assertNotNull($kelasValue);
            $this->assertNotEquals('', $kelasValue);
        } finally {
            if (file_exists($tempPath)) {
                @unlink($tempPath);
            }
        }
    }

    /**
     * Test 4: Akun admin/superadmin tanpa role divisi tetap dapat mengekspor Excel.
     */
    public function test_admin_user_can_export_excel_successfully()
    {
        $user = $this->createUser();
        $user->givePermissionTo('murid');
        $user->assignRole('admin');

        $response = $this->actingAs($user)
            ->get(route('murid.export_excel'));

        $response->assertStatus(200);
    }
}

