<?php

namespace App\Http\Controllers\BimbinganKonseling;

use App\Http\Controllers\Controller;
use App\Models\BimbinganKonseling\LaporanDesa;
use App\Models\MasterData\Tanggal;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

class LaporanDesaController extends Controller
{
    public function __construct()
    {
        $this->middleware(['permission:bimbingan_konseling']);
    }

    public function index(Request $request)
    {
        $tahun  = $request->has('tahun') ? $request->tahun : Carbon::now()->year;
        $bulan  = $request->has('bulan') ? $request->bulan : Carbon::now()->month;

        $listTahun = Tanggal::where('status', true)->orderBy('tahun', 'DESC')->groupBy('tahun')
                 ->pluck('tahun');
        $listBulan = Tanggal::where([['tahun', $tahun], ['status', true]])->orderBy('bulan', 'ASC')->groupBy('bulan')
                 ->pluck('bulan');

        $listKategori  = LaporanDesa::listKategori;
        $listRealisasi = LaporanDesa::listRealisasi;

        $datas = LaporanDesa::where([['tahun', $tahun], ['bulan', $bulan], ['status', true]])
                    ->with('createdBy', 'updatedBy')->orderBy('created_at', 'ASC')->get();

        return view('pages.bimbingan_konseling.laporan_desa.index', compact('listTahun', 'tahun', 'listBulan', 'bulan', 'listKategori', 'listRealisasi', 'datas'));
    }

    public function create($id = null)
    {
        $title = "Create";
        $data  = new LaporanDesa();

        $tahun  = Carbon::now()->year;
        $bulan  = Carbon::now()->month;

        if (!empty($id)) {
            $title = "Update";
            $data = LaporanDesa::findOrFail($id);

            $tahun  = $data->tahun;
            $bulan  = $data->bulan;
        }

        $listTahun  = Tanggal::where('status', true)->orderBy('tahun', 'DESC')->groupBy('tahun')->pluck('tahun');
        $listBulan  = Tanggal::listBulan;
        $listKategori  = LaporanDesa::listKategori;
        $listRealisasi = LaporanDesa::listRealisasi;

        $data->tanggal = $data->tanggal ? date('Y-m-d', $data->tanggal) : null;

        return view('pages.bimbingan_konseling.laporan_desa.create', compact('title', 'data', 'listTahun', 'tahun', 'listBulan', 'bulan', 'listKategori', 'listRealisasi'));
    }

    public function store(Request $request)
    {
        $status = "Berhasil";
        $action = "menambahkan";
        $title  = "Data Laporan Desa Periode ";

        $this->validate($request, [
            'program' => 'required|string',
            'tahun' => 'required|integer',
            'bulan' => 'required|integer',
            'kategori' => 'required|integer',
            'realisasi' => 'required|integer',
        ]);

        DB::beginTransaction();
        try {
            $periode = Tanggal::listBulan[$request->bulan] . " " . $request->tahun;

            if ($request->id != null && $request->id != '') {
                $data = LaporanDesa::findOrFail($request->id);
                $action = "memperbarui";

                if (($data->bulan.$data->tahun) != ($request->bulan.$request->tahun)) {
                    $periode = Tanggal::listBulan[$data->bulan] . " " . $data->tahun . " ke " . Tanggal::listBulan[$request->bulan] . " " . $request->tahun;
                }
            } else {
                $data = new LaporanDesa();
                $data->created_at = strtotime(Carbon::now());
                $data->created_by = Auth::id();
            }

            $data->tahun      = $request->tahun;
            $data->bulan      = $request->bulan;
            $data->program    = $request->program;
            $data->kategori   = $request->kategori;
            $data->realisasi  = $request->realisasi;
            $data->status     = true;
            $data->updated_at = strtotime(Carbon::now());
            $data->updated_by = Auth::id();
            $data->save();

            DB::commit();

            $message = $status . " " . $action . " " . $title . " " . $periode;

            toast($message, 'success');
            return redirect()->route('bimbingan_konseling.laporan_desa.index');
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
            $data = LaporanDesa::findOrFail($id);
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

    public function exportExcel(Request $request)
    {
        $tahun  = $request->has('tahun') ? $request->tahun : null;
        $bulan  = $request->has('bulan') ? $request->bulan : null;

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

        $title = "Laporan BK Desa";
		$spreadsheet = new Spreadsheet();
		$spreadsheet->getProperties()->setCreator("PJP Online Pagesangan II")->setLastModifiedBy("PJP Online Pagesangan II")->setTitle($title)->setSubject($title)->setDescription($title);
        $sheet = $spreadsheet->getActiveSheet();

        $fields = [
            ['field' => 'no', 'name' => 'No.'],
            ['field' => 'program', 'name' => 'Divisi'],
            ['field' => 'periode', 'name' => 'Periode'],
            ['field' => 'kategori', 'name' => 'Kategori'],
            ['field' => 'realisasi', 'name' => 'Realisasi'],
        ];

        $col = 1;
        $row = 1;

        $sheet->getColumnDimension($sheet->getCell($sheet->getCellByColumnAndRow($col, $row)->getCoordinate())->getColumn())->setWidth(2);
        $row++;  $col = 2;
        $sheet->setCellValueByColumnAndRow($col, $row, $title);
        $sheet->getStyle($sheet->getCellByColumnAndRow($col, $row)->getCoordinate())->getFont()->setBold(true);
        $sheet->mergeCellsByColumnAndRow($col, $row, $col+6, $row);
        $row++;
        $sheet->setCellValueByColumnAndRow($col, $row, "Periode : " . Tanggal::listBulan[$bulan] . " " . $tahun);
        $sheet->getStyle($sheet->getCellByColumnAndRow($col, $row)->getCoordinate())->getFont()->setBold(true);
        $sheet->mergeCellsByColumnAndRow($col, $row, $col+5, $row);
        $row++;
        $row++;

        $colAwal = $col;

        foreach ($fields as $field) {
            $sheet->setCellValueByColumnAndRow($col, $row, (string)$field['name']);
            $sheet->getColumnDimension($sheet->getCell($sheet->getCellByColumnAndRow($col, $row)->getCoordinate())->getColumn())->setAutoSize(true);
            $col++;
        }

        $colAkhir = $col-1;

        $sheet->getStyleByColumnAndRow($colAwal, $row, $col-1, $row)->applyFromArray($style_border);
        $sheet->getStyleByColumnAndRow($colAwal, $row, $col-1, $row)->applyFromArray($style_header);

        $row++;

        $datas = LaporanDesa::where([['tahun', $tahun], ['bulan', $bulan], ['status', true]])
                    ->with('createdBy', 'updatedBy')->orderBy('created_at', 'ASC')->get();

        $listKategori  = LaporanDesa::listKategori;
        $listRealisasi = LaporanDesa::listRealisasi;

        if (!empty($datas) && count($datas) > 0) {
            $rowAwal = $row;

            foreach ($datas as $index => $data) {
                $col = $colAwal;

                foreach ($fields as $field) {
                    if ($field['field'] == "no") {
                        $sheet->setCellValueByColumnAndRow($col, $row, ++$index . ".");
                        $sheet->getStyleByColumnAndRow($col, $row)->applyFromArray($style_center);
                    } else if ($field['field'] == "periode") {
                        $sheet->setCellValueByColumnAndRow($col, $row, Tanggal::listBulan[$data->bulan] . " " . $data->tahun);
                        $sheet->getStyleByColumnAndRow($col, $row)->applyFromArray($style_center);
                    } else if ($field['field'] == "kategori") {
                        $value = $listKategori[$data->kategori];
                        $sheet->setCellValueByColumnAndRow($col, $row, $value);
                    } else if ($field['field'] == "realisasi") {
                        $value = $listRealisasi[$data->realisasi] ?? '-';
                        $sheet->setCellValueByColumnAndRow($col, $row, $value);
                    } else {
                        $sheet->setCellValueByColumnAndRow($col, $row, $data[$field['field']]);
                    }

                    $sheet->getColumnDimension($sheet->getCell($sheet->getCellByColumnAndRow($col, $row)->getCoordinate())->getColumn())->setAutoSize(true);
                    $col++;
                }

                $row++;
            }

            $rowAkhir = $row-1;
            $sheet->getStyleByColumnAndRow($colAwal, $rowAwal, $colAkhir, $rowAkhir)->applyFromArray($style_border);
        } else {
            $sheet->setCellValueByColumnAndRow($colAwal, $row, "Data is empty");
            $sheet->mergeCells($sheet->getCellByColumnAndRow($colAwal, $row)->getCoordinate().':'.$sheet->getCellByColumnAndRow($colAkhir, $row)->getCoordinate());
            $sheet->getColumnDimension($sheet->getCell($sheet->getCellByColumnAndRow($col, $row)->getCoordinate())->getColumn())->setAutoSize(true);
            $sheet->getStyleByColumnAndRow($colAwal, $row, $colAkhir, $row)->applyFromArray($style_border);
            $sheet->getStyleByColumnAndRow($colAwal, $row, $colAkhir, $row)->applyFromArray($style_center);
        }

        // set sheet as the active sheet
        $spreadsheet->setActiveSheetIndex(0);

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
