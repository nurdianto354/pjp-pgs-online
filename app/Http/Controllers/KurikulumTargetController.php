<?php

namespace App\Http\Controllers;

use App\Models\KurikulumTarget\KurikulumTarget;
use App\Models\KurikulumTarget\KurikulumTargetDetail;
use App\Models\MasterData\Divisi;
use App\Models\MasterData\Karakter;
use App\Models\MasterData\Kelas;
use App\Models\MasterData\Materi;
use App\Models\MasterData\Satuan;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx as ReaderXlsx;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx as WriterXlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

class KurikulumTargetController extends Controller
{
    public function __construct()
    {
        $this->middleware(['permission:kurikulum_target']);
    }

    public function index(Request $request)
    {
        $user = Auth::user();
        $roles = $user->getRoleNames()->toArray();

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

            $kelas = Kelas::whereIn('divisi_id', $divisiIds)->where('status', true)->first();

            $listKelas = Kelas::whereIn('divisi_id', $divisiIds)->where('status', true)->get();
        } else {
            $kelas = Kelas::where([['status', true], ['nama', 'Paud A']])->first();
            $listKelas = Kelas::where('status', true)->get();
        }

        $kelasId   = $kelas->id;
        $kelasNama = $kelas->nama;

        if ($request->has('kelas_id')) {
            $kelasId   = $request->kelas_id;
        }

        if ($request->has('kelas_nama')) {
            $kelasNama = $request->kelas_nama;
        }

        $id = KurikulumTarget::where([['kelas_id', $kelasId]])->pluck('id')->first();
        $datas = KurikulumTargetDetail::with('getKarakter', 'getMateri', 'getSatuan')->where('kurikulum_target_id', $id)->orderBy('created_at', 'ASC')->get();

        return view('pages.kurikulum_target.index', compact('listKelas', 'id', 'kelasId', 'kelasNama', 'datas'));
    }

    public function create(Request $request)
    {
        $title = !empty($request['id']) ? "Perbarui" : "Tambah";

        $id = $request['id'];
        $kelasId = $request['kelas_id'];

        $kelasNama = Kelas::where([['status', true], ['id', $kelasId]])->pluck('nama')->first();

        $listKarakter = Karakter::where('status', true)->orderBy('nama', 'ASC')->get();
        $listMateri   = Materi::where('status', true)->orderBy('nama', 'ASC')->get();
        $listSatuan   = Satuan::where('status', true)->orderBy('nama', 'ASC')->get();

        return view('pages.kurikulum_target.create', compact('title', 'id', 'kelasId', 'kelasNama', 'listKarakter', 'listMateri', 'listSatuan'));
    }

    public function store(Request $request)
    {
        $kelasNama = Kelas::where([['id', $request['kelas_id']], ['status', true]])->pluck('nama')->first();

        DB::beginTransaction();
        try {
            $action = "menambahkan kurikulum target";
            $data = new KurikulumTarget();
            $data->created_at = Carbon::now();

            if ($request['id'] != null) {
                $action = "memperbarui kurikulum target";
                $data = KurikulumTarget::findOrFail($request['id']);
            }

            $data->kelas_id = $request['kelas_id'];
            $data->updated_at = Carbon::now();
            $data->save();

            if ($request->has('karakter') && $request->has('materi') && $request->has('satuan')) {
                foreach ($request['target'] as $index => $target) {
                    if ($request['id_detail'][$index] != null) {
                        $dataDetail = KurikulumTargetDetail::findOrFail($request['id_detail'][$index]);
                    } else {
                        $dataDetail = new KurikulumTargetDetail();
                        $dataDetail->karakter_id = $request['karakter'][$index];
                        $dataDetail->materi_id   = $request['materi'][$index];
                        $dataDetail->satuan_id   = $request['satuan'][$index];
                        $dataDetail->created_at  = Carbon::now();
                    }

                    $dataDetail->kurikulum_target_id = $data->id;
                    $dataDetail->target              = $target;
                    $dataDetail->updated_at          = Carbon::now();
                    $dataDetail->save();
                }
            }

            DB::commit();

            toast('Berhasil ' . $action . ' kelas ' . $kelasNama, 'success');
            return redirect()->route('kurikulum_target.index', ['kelas_id' => $request['kelas_id'], 'kelas_nama' => $kelasNama]);
        } catch (\Throwable $th) {
            DB::rollBack();

            toast('Gagal ' . $action . ' kelas ' . $kelasNama, 'error');
            return back();
        }
    }

    public function getDataDetail(Request $request)
    {
        $datas = KurikulumTargetDetail::where('kurikulum_target_id', $request['id'])->orderBy('created_at', 'ASC')->get();

        return response()->json([
            'datas' => $datas,
        ]);
    }

    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            $data = KurikulumTargetDetail::findOrFail($id);
            $data->delete();

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

    public function exportTemplate(Request $request)
    {
        $kelas = Kelas::where('id', $request['kelas_id'])->pluck('nama')->first();

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

		$title = "Template Import Data Kurikulum & Target";
		$spreadsheet = new Spreadsheet();
		$spreadsheet->getProperties()->setCreator("PJP Online Pagesangan II")->setLastModifiedBy("PJP Online Pagesangan II")->setTitle($title)->setSubject($title)->setDescription($title);

        // buat sheet dan beri nama data
		$sheet = $spreadsheet->getActiveSheet();
		$sheet->setTitle('Data');

		$col = $col_awal = 1;
		$row = 1;

		// start - set header template upload budget
		$sheet->setCellValueExplicitByColumnAndRow($col, $row, strtoupper($title), DataType::TYPE_STRING);
		$sheet->mergeCells($sheet->getCellByColumnAndRow($col, $row)->getCoordinate().':'.$sheet->getCellByColumnAndRow($col+7, $row)->getCoordinate());
		$sheet->getStyleByColumnAndRow($col, $row)->applyFromArray(['font' => ['size'  => 12, 'bold' => true], 'fill' => ['type' => Fill::FILL_SOLID]]);
        $row++;
		$sheet->setCellValueExplicitByColumnAndRow($col, $row, "PJP Online Pagesangan II", DataType::TYPE_STRING);
		$sheet->mergeCells($sheet->getCellByColumnAndRow($col, $row)->getCoordinate().':'.$sheet->getCellByColumnAndRow($col+7, $row)->getCoordinate());
        $row++;
		$sheet->setCellValueExplicitByColumnAndRow($col, $row, $kelas, DataType::TYPE_STRING);
		$sheet->mergeCells($sheet->getCellByColumnAndRow($col, $row)->getCoordinate().':'.$sheet->getCellByColumnAndRow($col+2, $row)->getCoordinate());
        $row++;

		$sheet->getStyleByColumnAndRow($col, $row-3, $col, $row-1)->applyFromArray(['font' => ['size'  => 10, 'bold' => true], 'fill' => ['type' => Fill::FILL_SOLID]]);

        $row++;
		$row_awal = $row;
		$col = $col_awal;

        $sheet->setCellValueExplicitByColumnAndRow($col, $row, "Nama Karakter", DataType::TYPE_STRING);
        $sheet->getColumnDimension($sheet->getCell($sheet->getCellByColumnAndRow($col, $row)->getCoordinate())->getColumn())->setAutoSize(true);
        $row++;
        $sheet->setCellValueExplicitByColumnAndRow($col, $row, "Nama Materi", DataType::TYPE_STRING);
        $sheet->getColumnDimension($sheet->getCell($sheet->getCellByColumnAndRow($col, $row)->getCoordinate())->getColumn())->setAutoSize(true);
        $row++;
        $sheet->setCellValueExplicitByColumnAndRow($col, $row, "Satuan Target", DataType::TYPE_STRING);
        $sheet->getColumnDimension($sheet->getCell($sheet->getCellByColumnAndRow($col, $row)->getCoordinate())->getColumn())->setAutoSize(true);
        $row++;
        $sheet->setCellValueExplicitByColumnAndRow($col, $row, "Target Kurikulum", DataType::TYPE_STRING);
        $sheet->getColumnDimension($sheet->getCell($sheet->getCellByColumnAndRow($col, $row)->getCoordinate())->getColumn())->setAutoSize(true);
        $row++;

		$sheet->getStyleByColumnAndRow($col, $row_awal, $col, $row-1)->applyFromArray($style_border);

        $sheet = $spreadsheet->createSheet();
        $sheet->setTitle('Master Data');

        $dataKarakters  = Karakter::where("status", true)->orderBy("nama", "ASC")->get();
        $dataMateries   = Materi::where("status", true)->orderBy("nama", "ASC")->get();
        $dataSatuans    = Satuan::where("status", true)->orderBy("nama", "ASC")->get();

        $col = $col_awal = $colTemp = 1;
		$row = $row_awal = 1;

        $sheet->setCellValueExplicitByColumnAndRow($col, $row, "Data Karakter", DataType::TYPE_STRING);
		$sheet->mergeCells($sheet->getCellByColumnAndRow($col, $row)->getCoordinate().':'.$sheet->getCellByColumnAndRow($col+1, $row)->getCoordinate());
        $sheet->getStyleByColumnAndRow($col, $row)->applyFromArray([
            'font' => ['size' => 12, 'bold' => true],
            'fill' => ['type' => Fill::FILL_SOLID],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER
            ]
        ]);

        $row++;

        $sheet->setCellValueExplicitByColumnAndRow($col, $row, "Kode", DataType::TYPE_STRING); $col++;
        $sheet->setCellValueExplicitByColumnAndRow($col, $row, "Nama", DataType::TYPE_STRING);
        $sheet->getStyleByColumnAndRow($col-1, $row, $col, $row)->applyFromArray([
            'font' => ['size' => 10, 'bold' => true],
            'fill' => ['type' => Fill::FILL_SOLID],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER
            ]
        ]);

        $row++;

        foreach ($dataKarakters as $data) {
            $col = $colTemp;
            $sheet->setCellValueExplicitByColumnAndRow($col, $row, $data->id, DataType::TYPE_STRING);
            $sheet->getColumnDimension($sheet->getCell($sheet->getCellByColumnAndRow($col, $row)->getCoordinate())->getColumn())->setAutoSize(true);
            $col++;
            $sheet->setCellValueExplicitByColumnAndRow($col, $row,  $data->nama, DataType::TYPE_STRING);
            $sheet->getColumnDimension($sheet->getCell($sheet->getCellByColumnAndRow($col, $row)->getCoordinate())->getColumn())->setAutoSize(true);
            $row++;
        }

		$sheet->getStyleByColumnAndRow($colTemp, $row_awal, $col, $row-1)->applyFromArray($style_border);

        $col++; $col++;
        $colTemp = $col;
        $row = $row_awal;

        $sheet->setCellValueExplicitByColumnAndRow($col, $row, "Data Materi", DataType::TYPE_STRING);
		$sheet->mergeCells($sheet->getCellByColumnAndRow($col, $row)->getCoordinate().':'.$sheet->getCellByColumnAndRow($col+1, $row)->getCoordinate());
        $sheet->getStyleByColumnAndRow($col, $row)->applyFromArray([
            'font' => ['size' => 12, 'bold' => true],
            'fill' => ['type' => Fill::FILL_SOLID],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER
            ]
        ]);

        $row++;

        $sheet->setCellValueExplicitByColumnAndRow($col, $row, "Kode", DataType::TYPE_STRING); $col++;
        $sheet->setCellValueExplicitByColumnAndRow($col, $row, "Nama", DataType::TYPE_STRING);
        $sheet->getStyleByColumnAndRow($col-1, $row, $col, $row)->applyFromArray([
            'font' => ['size' => 10, 'bold' => true],
            'fill' => ['type' => Fill::FILL_SOLID],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER
            ]
        ]);

        $row++;

        foreach ($dataMateries as $data) {
            $col = $colTemp;
            $sheet->setCellValueExplicitByColumnAndRow($col, $row, $data->id, DataType::TYPE_STRING);
            $sheet->getColumnDimension($sheet->getCell($sheet->getCellByColumnAndRow($col, $row)->getCoordinate())->getColumn())->setAutoSize(true);
            $col++;
            $sheet->setCellValueExplicitByColumnAndRow($col, $row,  $data->nama, DataType::TYPE_STRING);
            $sheet->getColumnDimension($sheet->getCell($sheet->getCellByColumnAndRow($col, $row)->getCoordinate())->getColumn())->setAutoSize(true);
            $row++;
        }

		$sheet->getStyleByColumnAndRow($colTemp, $row_awal, $col, $row-1)->applyFromArray($style_border);

        $col++; $col++;
        $colTemp = $col;
        $row = $row_awal;

        $sheet->setCellValueExplicitByColumnAndRow($col, $row, "Data Satuan", DataType::TYPE_STRING);
		$sheet->mergeCells($sheet->getCellByColumnAndRow($col, $row)->getCoordinate().':'.$sheet->getCellByColumnAndRow($col+1, $row)->getCoordinate());
        $sheet->getStyleByColumnAndRow($col, $row)->applyFromArray([
            'font' => ['size' => 12, 'bold' => true],
            'fill' => ['type' => Fill::FILL_SOLID],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER
            ]
        ]);

        $row++;

        $sheet->setCellValueExplicitByColumnAndRow($col, $row, "Kode", DataType::TYPE_STRING); $col++;
        $sheet->setCellValueExplicitByColumnAndRow($col, $row, "Nama", DataType::TYPE_STRING);
        $sheet->getStyleByColumnAndRow($col-1, $row, $col, $row)->applyFromArray([
            'font' => ['size' => 10, 'bold' => true],
            'fill' => ['type' => Fill::FILL_SOLID],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER
            ]
        ]);

        $row++;

        foreach ($dataSatuans as $data) {
            $col = $colTemp;
            $sheet->setCellValueExplicitByColumnAndRow($col, $row, $data->id, DataType::TYPE_STRING);
            $sheet->getColumnDimension($sheet->getCell($sheet->getCellByColumnAndRow($col, $row)->getCoordinate())->getColumn())->setAutoSize(true);
            $col++;
            $sheet->setCellValueExplicitByColumnAndRow($col, $row,  $data->nama, DataType::TYPE_STRING);
            $sheet->getColumnDimension($sheet->getCell($sheet->getCellByColumnAndRow($col, $row)->getCoordinate())->getColumn())->setAutoSize(true);
            $row++;
        }

		$sheet->getStyleByColumnAndRow($colTemp, $row_awal, $col, $row-1)->applyFromArray($style_border);

        // set sheet as the active sheet
		$spreadsheet->setActiveSheetIndexByName('Data');

        $writer = new WriterXlsx($spreadsheet);
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

    public function importData(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'file' => 'required|mimes:csv,xls,xlsx|max:2048'
        ]);

        if($validator->fails()) {
            toast('Gagal import data kurikulum & target, karena file bermasalah', 'error');
            return back();
        }

        $reader = new ReaderXlsx();
        $inputFileType = 'Xlsx';
        $inputFileName = $request->file;
        $reader = IOFactory::createReader($inputFileType);
        $spreadsheet = $reader->load($inputFileName);
        $sheetData = $spreadsheet->getActiveSheet();
        $highestColumn = $sheetData->getHighestColumn();
        $highestColumnIndex = Coordinate::columnIndexFromString($highestColumn);

        $kelas = $sheetData->getCellByColumnAndRow(1, 3)->getValue();

        $kelasId = Kelas::where([['nama', 'LIKE', $kelas.'%'], ['status', true]])->pluck('id')->first();

        if (empty($kelasId)) {
            toast('Gagal import data kurikulum & target kelas '.$kelas.', karena kelas tidak di temukan', 'Error');
            return back();
        }

        $id = KurikulumTarget::where([['kelas_id', $kelasId]])->pluck('id')->first();

        if (!empty($id)) {
            toast('Gagal import data kurikulum & target kelas '.$kelas.', karena sudah ada', 'Error');
            return back();
        }

        $dataKarakters  = Karakter::where("status", true)->get()
            ->mapWithKeys(function ($field) {
                return [strtolower($field->nama) => $field->id];
            })->toArray();

        $dataMateries   = Materi::where("status", true)->get()
            ->mapWithKeys(function ($field) {
                return [strtolower($field->nama) => $field->id];
            })->toArray();

        $dataSatuans    = Satuan::where("status", true)->get()
            ->mapWithKeys(function ($field) {
                return [strtolower($field->nama) => $field->id];
            })->toArray();

        DB::beginTransaction();
        try {
            $data = new KurikulumTarget();
            $data->kelas_id   = $kelasId;
            $data->created_at = Carbon::now();
            $data->updated_at = Carbon::now();
            $data->save();

            for ($col = 2; $col <= $highestColumnIndex; $col++) {
                $row = 6;

                $namaKarakter = $sheetData->getCellByColumnAndRow($col, $row)->getValue();
                $row++;
                $namaMateri   = $sheetData->getCellByColumnAndRow($col, $row)->getValue();
                $row++;
                $namaSatuan   = $sheetData->getCellByColumnAndRow($col, $row)->getValue();
                $row++;
                $target       = $sheetData->getCellByColumnAndRow($col, $row)->getValue();

                if (empty($namaKarakter) && empty($namaMateri) && empty($namaSatuan)) {
                    continue;
                }

                if (empty($namaKarakter) || empty($namaMateri) || empty($namaSatuan)) {
                    DB::rollback();

                    toast('Gagal import data kurikulum & target kelas '.$kelas.', mohon cek kembali data import karena tidak boleh kosong', 'Error');
                    return back();
                }

                $karakterId = !empty($dataKarakters[strtolower($namaKarakter)]) ? $dataKarakters[strtolower($namaKarakter)] : null;
                $materiId   = !empty($dataMateries[strtolower($namaMateri)]) ? $dataMateries[strtolower($namaMateri)] : null;
                $satuanId   = !empty($dataSatuans[strtolower($namaSatuan)]) ? $dataSatuans[strtolower($namaSatuan)] : null;

                if (empty($karakterId) || empty($materiId) || empty($satuanId)) {
                    DB::rollback();
                    $error = [];

                    if (empty($karakterId)) {
                        $error[] = "data karakter";
                    }

                    if (empty($materiId)) {
                        $error[] = "data materi";
                    }

                    if (empty($satuanId)) {
                        $error[] = "data satuan";
                    }

                    $mesageError = implode(', ', $error);

                    toast('Gagal import data kurikulum & target kelas '.$kelas.', karena '.$mesageError.' tidak ada di master data', 'Error');
                    return back();
                }

                $dataDetail = new KurikulumTargetDetail();
                $dataDetail->kurikulum_target_id = $data->id;
                $dataDetail->karakter_id         = $karakterId;
                $dataDetail->materi_id           = $materiId;
                $dataDetail->target              = $target;
                $dataDetail->satuan_id           = $satuanId;
                $dataDetail->updated_at          = Carbon::now();
                $dataDetail->save();
            }

            DB::commit();

            toast('Berhasil import data kurikulum & target kelas '.$kelas, 'success');
            return back();
        } catch (\Exception $e) {
            DB::rollback();

            toast('Gagal import data kurikulum & target kelas '.$kelas, 'error');
            return back();
        }
    }
}
