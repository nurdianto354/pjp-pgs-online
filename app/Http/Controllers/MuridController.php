<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\MasterData\Divisi;
use App\Models\MasterData\Kelas;
use App\Models\Murid\Murid;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

class MuridController extends Controller
{
    public function __construct()
    {
        $this->middleware(['permission:murid']);
    }

    public function index()
    {
        $user = Auth::user();
        $roles = $user->getRoleNames()->toArray();

        if (array_intersect($roles, ['paud', 'caberawit', 'praremaja', 'remaja', 'pranikah'])) {
            $divisiIds = [];
            $kelasIds = [];

            // Loop untuk mengambil divisi dan kelas berdasarkan peran
            foreach ($roles as $role) {
                $divisi = Divisi::select('id')
                    ->where([['nama', ucfirst(strtolower($role))], ['status', true]])
                    ->first();

                // Pastikan divisi ditemukan sebelum melanjutkan
                if ($divisi) {
                    $divisiIds[] = $divisi->id; // Simpan ID Divisi
                    $kelasIds = array_merge($kelasIds, Kelas::select('id')
                        ->where([['divisi_id', $divisi->id], ['status', true]])
                        ->pluck('id')->toArray());
                }
            }

            // Ambil list divisi berdasarkan ID yang ditemukan
            $listDivisi = Divisi::select('id', 'nama')->whereIn('id', $divisiIds)
                ->where('status', true)
                ->get();

            // Ambil list kelas berdasarkan ID kelas yang ditemukan
            $listKelas = Kelas::select('id', 'nama')->whereIn('id', $kelasIds)
                ->where('status', true)
                ->get();

            $datas = Murid::with('getKelas')->whereIn('divisi_id', $divisiIds)->get();
        } else {
            // Jika tidak ada role yang sesuai, ambil semua divisi dan kelas dengan status true
            $listDivisi = Divisi::select('id', 'nama')->where('status', true)
                ->get();

            $listKelas = Kelas::select('id', 'nama')->where('status', true)
                ->get();

            // Ambil data murid dengan relasi ke kelas
            $datas = Murid::with('getKelas')->get();
        }

        // Return view dengan data yang sudah disiapkan
        return view('pages.murid.index', compact('listDivisi', 'listKelas', 'datas'));

    }

    public function create(Request $request)
    {
        $status = "Berhasil";
        $action = "menambahkan";
        $title  = "Data Murid";

        DB::beginTransaction();
        try {
            if ($request->id != null && $request->id != '') {
                $data = Murid::findOrFail($request->id);
                $action = "memperbarui";
            } else {
                $data = new Murid();
                $data->created_at = Carbon::now();
            }

            $data->nama_lengkap   = ucwords(strtolower($request->nama_lengkap));
            $data->nama_panggilan = ucwords(strtolower($request->nama_panggilan));
            $data->jenis_kelamin  = $request->jenis_kelamin;
            $data->divisi_id      = $request->divisi_id;
            $data->kelas_id       = $request->kelas_id;
            $data->tempat_lahir   = $request->tempat_lahir;
            $data->tanggal_lahir  = $request->tanggal_lahir;
            $data->alamat         = $request->alamat;
            $data->no_telp        = $request->no_telp;
            $data->status         = true;
            $data->updated_at     = Carbon::now();
            $data->save();

            DB::commit();

            $message = $status . " " . $action . " " . $title;

            toast($message, 'success');
            return back();
        } catch (\Exception $e) {
            DB::rollback();

            toast('Gagal. Mohon cek kembali','error');
            return back();
        }
    }

    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            $data = Murid::findOrFail($id);
            $data->status = false;
            $data->save();

            DB::commit();

            return response()->json([
                'status'     => 'success',
                'keterangan' => '',
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();

            return response()->json([
                'status'     => 'error',
                'keterangan' => 'karena ada kesalahan di sistem'
            ]);
        }
    }

    public function exportExcel()
    {
        $user = Auth::user();
        $roles = $user->getRoleNames()->toArray();

        // Jika tidak ada role yang sesuai, ambil semua divisi dan kelas dengan status true
        $listDivisi = Divisi::select('id', 'nama')->where('status', true)
            ->get();

        if (array_intersect($roles, ['paud', 'caberawit', 'praremaja', 'remaja', 'pranikah'])) {
            $divisiIds = [];

            // Loop untuk mengambil divisi dan kelas berdasarkan peran
            foreach ($roles as $role) {
                $divisi = Divisi::select('id')
                    ->where([['nama', ucfirst(strtolower($role))], ['status', true]])
                    ->first();

                // Pastikan divisi ditemukan sebelum melanjutkan
                if ($divisi) {
                    $divisiIds[] = $divisi->id; // Simpan ID Divisi
                }
            }

            // Ambil list divisi berdasarkan ID yang ditemukan
            $listDivisi = Divisi::select('id', 'nama')->whereIn('id', $divisiIds)
                ->where('status', true)
                ->get();
        }

        $style_border = [
			'borders' => [
				'allBorders' => [
					'borderStyle' => Border::BORDER_THIN,
				],
			],
			'alignment' => [
				'vertical' => Alignment::VERTICAL_CENTER,
				'wrapText' => true,
			],
		];

		$style_header = [
			'alignment' => [
				'horizontal' => Alignment::HORIZONTAL_CENTER,
				'vertical' => Alignment::VERTICAL_CENTER,
				'wrapText' => true,
			],
			'fill'  => [
				'fillType' => Fill::FILL_SOLID,
				'startColor' => ['rgb' => 'EEEEEE'],
			],
			'font' => [
				'size' => 10,
				'bold' => true,
			],
		];

        $style_right = ['alignment' => ['horizontal' => Alignment::HORIZONTAL_RIGHT]];
        $style_left = ['alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT]];
        $style_center = ['alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]];

        $fields = [
            ['field' => 'no', 'name' => 'No.', 'style' => 'center'],
            ['field' => 'nama_lengkap', 'name' => 'Nama Lengkap', 'style' => 'left'],
            ['field' => 'nama_panggilan', 'name' => 'Nama Panggilan', 'style' => 'left'],
            ['field' => 'jenis_kelamin', 'name' => 'Jenis Kelamin', 'style' => 'left'],
            ['field' => 'kelas_id', 'name' => 'Kelas', 'style' => 'left'],
            ['field' => 'tempat_lahir', 'name' => 'Tempat Lahir', 'style' => 'left'],
            ['field' => 'tanggal_lahir', 'name' => 'Tanggal Lahir', 'style' => 'center'],
            ['field' => 'alamat', 'name' => 'Alamat', 'style' => 'left'],
            ['field' => 'no_telp', 'name' => 'No. Telp', 'style' => 'left'],
        ];

		$title = "Data Murid";
        $sheetActiveName = "Paud";

        $spreadsheet = new Spreadsheet();
        $spreadsheet->getProperties()->setCreator("PJP Online Pagesangan II")->setLastModifiedBy("PJP Online Pagesangan II")->setTitle($title)->setSubject($title)->setDescription($title);


        foreach($listDivisi as $key => $divisi) {
            if ($key <= 0) {
                $sheetActiveName = $divisi->nama;

                $sheet = $spreadsheet->getActiveSheet();
            } else {
                $sheet = $spreadsheet->createSheet();
            }

            $sheet->setTitle($divisi->nama);

            $col = $colAwal = 1;
            $row = 1;

            foreach ($fields as $field) {
                $sheet->setCellValueExplicitByColumnAndRow($col, $row, (string)$field['name'], DataType::TYPE_STRING);
                $sheet->getColumnDimension($sheet->getCell($sheet->getCellByColumnAndRow($col, $row)->getCoordinate())->getColumn())->setAutoSize(true);
                $col++;
            }

            $sheet->getStyleByColumnAndRow($colAwal, $row, $col-1, $row)->applyFromArray($style_border);
            $sheet->getStyleByColumnAndRow($colAwal, $row, $col-1, $row)->applyFromArray($style_header);

            $row++;
            $rowAwal = $row;


            $listMurid = Murid::with('getKelas')
                ->where([['divisi_id', $divisi->id], ['status', true]])
                ->orderBy('kelas_id', 'ASC')
                ->orderBy('nama_lengkap', 'ASC')
                ->get();


            foreach ($listMurid as $index => $murid) {
                $col = $colAwal;

                foreach ($fields as $field) {

                    if ($field['field'] == 'no') {
                        $sheet->setCellValueExplicitByColumnAndRow($col, $row, ++$index, DataType::TYPE_NUMERIC);
                        $sheet->getColumnDimension($sheet->getCell($sheet->getCellByColumnAndRow($col, $row)->getCoordinate())->getColumn())->setAutoSize(true);
                        $col++;
                    } else {
                        if ($field['field'] == 'jenis_kelamin') {
                            $value = $murid[$field['field']] == 1 ? "Laki - laki" : "Perempuan";
                            $sheet->setCellValueExplicitByColumnAndRow($col, $row, (string)$value, DataType::TYPE_STRING);
                        } else if ($field['field'] == 'kelas_id') {
                            $value = !empty($murid->getKelas) ? $murid->getKelas->nama : '-';
                            $sheet->setCellValueExplicitByColumnAndRow($col, $row, (string)$value, DataType::TYPE_STRING);
                        } else {
                            $sheet->setCellValueExplicitByColumnAndRow($col, $row, (string)$murid[$field['field']], DataType::TYPE_STRING);
                        }

                        $sheet->getColumnDimension($sheet->getCell($sheet->getCellByColumnAndRow($col, $row)->getCoordinate())->getColumn())->setAutoSize(true);
                        $col++;
                    }

                    if ($field['style'] == 'left') {
                        $sheet->getStyle($sheet->getCellByColumnAndRow($col-1, $row)->getCoordinate())->applyFromArray($style_left);
                    } else if ($field['style'] == 'right') {
                        $sheet->getStyle($sheet->getCellByColumnAndRow($col-1, $row)->getCoordinate())->applyFromArray($style_right);
                    } else {
                        $sheet->getStyle($sheet->getCellByColumnAndRow($col-1, $row)->getCoordinate())->applyFromArray($style_center);
                    }
                }

                $row++;
            }

            $sheet->getStyleByColumnAndRow($colAwal, $rowAwal, $col-1, $row-1)->applyFromArray($style_border);
        }

        // set sheet as the active sheet
		$spreadsheet->setActiveSheetIndexByName($sheetActiveName);

        $writer = new Xlsx($spreadsheet);
        $response = new StreamedResponse(
            function () use ($writer) {
                $writer->save('php://output');
            }
        );

        $response->headers->set('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $response->headers->set('Content-Disposition', 'attachment; filename="'.$title.'.xlsx"');
        $response->headers->set('Cache-Control', 'max-age=0');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', '0');

        return $response;
    }
}
