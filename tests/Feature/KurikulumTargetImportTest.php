<?php

namespace Tests\Feature;

use App\Models\KurikulumTarget\KurikulumTarget;
use App\Models\KurikulumTarget\KurikulumTargetDetail;
use App\Models\MasterData\Divisi;
use App\Models\MasterData\Karakter;
use App\Models\MasterData\Kelas;
use App\Models\MasterData\Materi;
use App\Models\MasterData\Satuan;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx as XlsxReader;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx as XlsxWriter;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class KurikulumTargetImportTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        Permission::findOrCreate('kurikulum_target');
    }

    public function test_export_template_and_import_kurikulum_target_succeeds()
    {
        $user = User::create([
            'username' => 'kurikulum_user_' . uniqid(),
            'nama'     => 'Kurikulum Officer',
            'email'    => 'kt_' . uniqid() . '@example.test',
            'password' => Hash::make('password123'),
            'no_telp'  => '081234567890',
            'status'   => true,
        ]);
        $user->givePermissionTo('kurikulum_target');

        $divisi = Divisi::firstOrCreate(
            ['nama' => 'Paud'],
            ['status' => true]
        );

        $uniqueClass = 'Kelas Test ' . uniqid();
        $kelas = Kelas::create([
            'nama'      => $uniqueClass,
            'divisi_id' => $divisi->id,
            'status'    => true,
        ]);

        $karakter = Karakter::firstOrCreate(['nama' => 'Jujur'], ['status' => true]);
        $materi   = Materi::firstOrCreate(['nama' => 'Al-Quran'], ['status' => true]);
        $satuan   = Satuan::firstOrCreate(['nama' => 'Ayat'], ['status' => true]);

        // 1. Export template
        $response = $this->actingAs($user)
            ->get(route('kurikulum_target.export_template', ['kelas_id' => $kelas->id]));
        $response->assertStatus(200);

        $content = $response->streamedContent();
        $tempPath = tempnam(sys_get_temp_dir(), 'test_template_') . '.xlsx';
        file_put_contents($tempPath, $content);

        // 2. Load template, add 1 column of data in col 2 (B)
        $reader = new XlsxReader();
        $spreadsheet = $reader->load($tempPath);
        $sheet = $spreadsheet->getActiveSheet();

        // Col 2 (B), Row 6: Karakter, Row 7: Materi, Row 8: Satuan, Row 9: Target
        $sheet->setCellValue('B6', $karakter->nama);
        $sheet->setCellValue('B7', $materi->nama);
        $sheet->setCellValue('B8', $satuan->nama);
        $sheet->setCellValue('B9', 10);

        $writer = new XlsxWriter($spreadsheet);
        $writer->save($tempPath);

        // 3. Post to importData
        $uploadedFile = new UploadedFile(
            $tempPath,
            'import.xlsx',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            null,
            true
        );

        $importResponse = $this->actingAs($user)
            ->post(route('kurikulum_target.import_data'), [
                'file' => $uploadedFile,
            ]);

        $importResponse->assertSessionHasNoErrors();

        // Verify in DB
        $kurikulumTarget = KurikulumTarget::where('kelas_id', $kelas->id)->first();
        $this->assertNotNull($kurikulumTarget, "KurikulumTarget should be created");

        $detail = KurikulumTargetDetail::where('kurikulum_target_id', $kurikulumTarget->id)->first();
        $this->assertNotNull($detail, "KurikulumTargetDetail should be created");
        $this->assertEquals($karakter->id, $detail->karakter_id);
        $this->assertEquals($materi->id, $detail->materi_id);
        $this->assertEquals($satuan->id, $detail->satuan_id);
        $this->assertEquals(10, $detail->target);

        if (file_exists($tempPath)) {
            @unlink($tempPath);
        }
    }
}

