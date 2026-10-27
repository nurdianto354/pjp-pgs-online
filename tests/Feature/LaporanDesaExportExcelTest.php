<?php

namespace Tests\Feature;

use App\Models\BimbinganKonseling\LaporanDesa;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx as XlsxReader;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class LaporanDesaExportExcelTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        Permission::findOrCreate('bimbingan_konseling');
    }

    public function test_laporan_desa_export_excel_with_various_realisasi()
    {
        $user = User::create([
            'username' => 'user_bk_' . uniqid(),
            'nama'     => 'BK Officer',
            'email'    => 'bk_' . uniqid() . '@example.test',
            'password' => Hash::make('password123'),
            'no_telp'  => '081234567890',
            'status'   => true,
        ]);
        $user->givePermissionTo('bimbingan_konseling');

        LaporanDesa::create([
            'tahun'      => 2025,
            'bulan'      => 1,
            'program'    => 'Divisi Remaja',
            'kategori'   => 1, // Bimbingan
            'realisasi'  => 0, // Belum (Previously crashed with undefined index 0)
            'status'     => true,
            'created_at' => strtotime(now()),
            'updated_at' => strtotime(now()),
            'created_by' => $user->id,
            'updated_by' => $user->id,
        ]);

        $response = $this->actingAs($user)
            ->get('/bimbingan-konseling/laporan-desa/export-excel?tahun=2025&bulan=1');

        $response->assertStatus(200);

        $content = $response->streamedContent();
        $tempPath = tempnam(sys_get_temp_dir(), 'test_desa_') . '.xlsx';
        file_put_contents($tempPath, $content);

        try {
            $reader = new XlsxReader();
            $spreadsheet = $reader->load($tempPath);
            $sheet = $spreadsheet->getActiveSheet();

            $allValues = [];
            foreach ($sheet->getRowIterator() as $row) {
                $cellIterator = $row->getCellIterator();
                $cellIterator->setIterateOnlyExistingCells(false);
                foreach ($cellIterator as $cell) {
                    $val = $cell->getValue();
                    if ($val !== null && $val !== '') {
                        $allValues[] = (string)$val;
                    }
                }
            }
            $this->assertContains('Belum', $allValues, "The exported excel should contain 'Belum' for realisasi=0");
        } finally {
            if (file_exists($tempPath)) {
                @unlink($tempPath);
            }
        }
    }
}

