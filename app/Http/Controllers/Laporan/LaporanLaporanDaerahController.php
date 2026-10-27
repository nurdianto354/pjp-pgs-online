<?php

namespace App\Http\Controllers\Laporan;

use App\Http\Controllers\Controller;
use App\Models\Absensi\Absensi;
use App\Models\Aktivitas\HariLibur;
use App\Models\Aktivitas\Jadwal;
use App\Models\KurikulumTarget\KurikulumTarget;
use App\Models\KurikulumTarget\KurikulumTargetDetail;
use App\Models\MasterData\Divisi;
use App\Models\MasterData\Kelas;
use App\Models\MasterData\Tanggal;
use App\Models\Murid\Murid;
use App\Models\PencapaianTarget\PencapaianTarget;
use Carbon\Carbon;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

class   LaporanLaporanDaerahController extends Controller
{
    public function __construct()
    {
        $this->middleware(['permission:laporan']);
    }

    public function index(Request $request)
    {
        $divisi = Divisi::where([['status', true], ['nama', 'Paud']])->first();
        $listDivisi = Divisi::where('status', true)->orderBy('id', 'ASC')->get();

        $divisiId   = $divisi->id;
        $divisiNama = $divisi->nama;

        if ($request->has('divisi_id')) {
            $divisi = Divisi::where([['status', true], ['id', $request->divisi_id]])->first();

            $divisiId   = $divisi->id;
            $divisiNama = $divisi->nama;
        }

        $tahun  = $request->has('tahun') ? $request->tahun : Carbon::now()->year;
        $bulan  = $request->has('bulan') ? $request->bulan : Carbon::now()->month;

        $listTahun = Tanggal::where('status', true)->orderBy('tahun', 'DESC')->groupBy('tahun')
                 ->pluck('tahun');
        $listBulan = Tanggal::where([['tahun', $tahun], ['status', true]])->orderBy('bulan', 'ASC')->groupBy('bulan')
                 ->pluck('bulan');

        return view('pages.laporan.laporan_daerah.index', compact('listTahun', 'tahun', 'listBulan', 'bulan', 'listDivisi', 'divisiId', 'divisiNama'));
    }

    public function exportExcel(Request $request)
    {
        $divisiNama = Divisi::where('id', $request['divisi_id'])->first()->nama;
        $listKelas  = Kelas::where([['divisi_id', $request['divisi_id']], ['status', true]])->orderBy('level', 'ASC')->get();

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

		$title = "Laporan KBM Divisi ".$divisiNama;
		$spreadsheet = new Spreadsheet();
		$spreadsheet->getProperties()->setCreator("PJP Online Pagesangan II")->setLastModifiedBy("PJP Online Pagesangan II")->setTitle($title)->setSubject($title)->setDescription($title);

        foreach($listKelas as $key => $kelas) {
            $fields = [
                ['field' => 'no', 'name' => 'No.', 'style' => 'center', 'col' => 1, 'row' => 2],
                ['field' => 'nama_lengkap', 'name' => 'Nama Lengkap Murid', 'style' => 'center', 'col' => 1, 'row' => 2],
            ];

            if ($key <= 0) {
                $sheet = $spreadsheet->getActiveSheet();
            } else {
                $sheet = $spreadsheet->createSheet();
            }

            $sheet->setTitle($kelas->nama);

            $id = KurikulumTarget::where([['kelas_id', $kelas->id]])->pluck('id')->first();
            $listTargetKurikulum = KurikulumTargetDetail::with('getKarakter', 'getMateri', 'getSatuan')
                ->where('kurikulum_target_id', $id)
                ->orderBy('karakter_id', 'ASC')
                ->orderBy('created_at', 'ASC')
                ->get();

            $col = $colAwal = 1;
            $row = $rowAwal = 1;

            foreach ($fields as $field) {
                $sheet->setCellValueExplicitByColumnAndRow($col, $row, (string)$field['name'], DataType::TYPE_STRING);
				$sheet->mergeCells($sheet->getCellByColumnAndRow($col, $row)->getCoordinate().':'.$sheet->getCellByColumnAndRow($col, $row+$field['row'])->getCoordinate());
                $sheet->getColumnDimension($sheet->getCell($sheet->getCellByColumnAndRow($col, $row)->getCoordinate())->getColumn())->setAutoSize(true);
                $col++;
            }

            foreach ($listTargetKurikulum as $targetKurikulum) {
                $row = $rowAwal;
                $sheet->setCellValueExplicitByColumnAndRow($col, $row, (string)$targetKurikulum->getKarakter->nama, DataType::TYPE_STRING);
                $sheet->getColumnDimension($sheet->getCell($sheet->getCellByColumnAndRow($col, $row)->getCoordinate())->getColumn())->setAutoSize(true);
                $row++;
                $sheet->setCellValueExplicitByColumnAndRow($col, $row, (string)$targetKurikulum->getMateri->nama, DataType::TYPE_STRING);
                $sheet->getColumnDimension($sheet->getCell($sheet->getCellByColumnAndRow($col, $row)->getCoordinate())->getColumn())->setAutoSize(true);
                $row++;
                $sheet->setCellValueExplicitByColumnAndRow($col, $row, ("(" . (string)$targetKurikulum->target . " " . (string)$targetKurikulum->getSatuan->nama . ")"), DataType::TYPE_STRING);
                $sheet->getColumnDimension($sheet->getCell($sheet->getCellByColumnAndRow($col, $row)->getCoordinate())->getColumn())->setAutoSize(true);
                $row++;
                $col++;
            }

            $fields = [
                ['field' => 'absensi_kehadiran', 'name' => 'Absensi Kehadiran', 'style' => 'center', 'col' => 5, 'row' => 0],
                ['field' => 'hadir', 'name' => 'Hadir', 'style' => 'center', 'col' => 0, 'row' => 1],
                ['field' => 'izin', 'name' => 'Izin', 'style' => 'center', 'col' => 0, 'row' => 1],
                ['field' => 'alfa', 'name' => 'Alfa', 'style' => 'center', 'col' => 0, 'row' => 1],
                ['field' => 'total', 'name' => 'Total', 'style' => 'center', 'col' => 0, 'row' => 1],
                ['field' => 'presentase', 'name' => 'Presentase', 'style' => 'center', 'col' => 1, 'row' => 0],
                ['field' => 'hadir_pers', 'name' => 'Hadir', 'style' => 'center', 'col' => 0, 'row' => 0],
                ['field' => 'hadir_izin_pers', 'name' => 'Hadir + Izin', 'style' => 'center', 'col' => 0, 'row' => 0],
            ];

            $row = $rowAwal;

            foreach ($fields as $field) {
                $sheet->setCellValueExplicitByColumnAndRow($col, $row, (string)$field['name'], DataType::TYPE_STRING);
                $sheet->mergeCells($sheet->getCellByColumnAndRow($col, $row)->getCoordinate().':'.$sheet->getCellByColumnAndRow($col+$field['col'], $row+$field['row'])->getCoordinate());
                $sheet->getColumnDimension($sheet->getCell($sheet->getCellByColumnAndRow($col, $row)->getCoordinate())->getColumn())->setAutoSize(true);

                if ($field['col'] == 0) {
                    $col++;
                }

                if ($field['row'] == 0 && $field['col'] != 0) {
                    $row++;
                }
            }

            $row++;

            $sheet->getStyleByColumnAndRow($colAwal, $rowAwal, $col-1, $row-1)->applyFromArray($style_border);
            $sheet->getStyleByColumnAndRow($colAwal, $rowAwal, $col-1, $row-1)->applyFromArray($style_header);

            $rowAwal = $row;

            $jadwal = Jadwal::where([['divisi_id', $request['divisi_id']], ['status', true]])
                ->pluck('hari');

            $hariLibur = HariLibur::where([['divisi_id', $request['divisi_id']], ['status', true]])
                ->pluck('tanggal');

            $listTanggal = Tanggal::where([['tahun', $request['tahun']], ['bulan', $request['bulan']], ['status', true]])
                ->whereIn('hari', $jadwal)
                ->whereNotIn('tanggal', $hariLibur)
                ->orderBy('tanggal', 'ASC')->pluck('tanggal')->toArray();

            $total = count($listTanggal);

            $listMurid = Murid::with('getKelas')
                ->where([['kelas_id', $kelas->id], ['status', true]])
                ->orderBy('nama_lengkap', 'ASC')
                ->get();

            foreach ($listMurid as $index => $murid) {
                $col = $colAwal;
                $sheet->setCellValueExplicitByColumnAndRow($col, $row, (int)++$index, DataType::TYPE_NUMERIC);
                $sheet->getColumnDimension($sheet->getCell($sheet->getCellByColumnAndRow($col, $row)->getCoordinate())->getColumn())->setAutoSize(true);
                $col++;
                $sheet->setCellValueExplicitByColumnAndRow($col, $row, (string)$murid->nama_lengkap, DataType::TYPE_STRING);
                $sheet->getColumnDimension($sheet->getCell($sheet->getCellByColumnAndRow($col, $row)->getCoordinate())->getColumn())->setAutoSize(true);
                $col++;

                $datas = PencapaianTarget::select('id', 'kurikulum_target_detail_id', 'target')
                    ->where([['kelas_id', $kelas->id], ['murid_id', $murid->id], ['tahun', $request['tahun']], ['bulan', $request['bulan']]])
                    ->get()->toArray();

                $listPencapaianTarget = [];

                foreach ($datas as $data) {
                    $listPencapaianTarget[$murid->id][$data['kurikulum_target_detail_id']] = $data;
                }

                foreach ($listTargetKurikulum as $targetKurikulum) {
                    $target = $listPencapaianTarget[$murid->id][$targetKurikulum->id]['target'] ?? 0;
                    $sheet->setCellValueExplicitByColumnAndRow($col, $row, (int)$target, DataType::TYPE_NUMERIC);
                    $sheet->getColumnDimension($sheet->getCell($sheet->getCellByColumnAndRow($col, $row)->getCoordinate())->getColumn())->setAutoSize(true);
                    $col++;
                }

                $listAbsensi = Absensi::where([['murid_id', $murid->id], ['kelas_id', $kelas->id]])
                    ->whereIn('tanggal', $listTanggal);

                $absensiCount = $listAbsensi->selectRaw('
                        SUM(CASE WHEN kehadiran = "H" THEN 1 ELSE 0 END) as hadir,
                        SUM(CASE WHEN kehadiran IN ("I", "S") THEN 1 ELSE 0 END) as izin,
                        SUM(CASE WHEN kehadiran = "A" THEN 1 ELSE 0 END) as alfa
                    ')
                    ->first();

                $hadir = (int)($absensiCount->hadir ?? 0);
                $izin  = (int)($absensiCount->izin ?? 0);
                $alfa  = (int)($absensiCount->alfa ?? 0);

                $hadirPers = $total > 0 ? round(($hadir / $total) * 100, 2) : 0;
                $hadirIzinPers = $total > 0 ? round((($hadir + $izin) / $total) * 100, 2) : 0;

                $sheet->setCellValueExplicitByColumnAndRow($col, $row, (int)$hadir, DataType::TYPE_NUMERIC);
                $sheet->getColumnDimension($sheet->getCell($sheet->getCellByColumnAndRow($col, $row)->getCoordinate())->getColumn())->setAutoSize(true);
                $col++;

                $sheet->setCellValueExplicitByColumnAndRow($col, $row, (int)$izin, DataType::TYPE_NUMERIC);
                $sheet->getColumnDimension($sheet->getCell($sheet->getCellByColumnAndRow($col, $row)->getCoordinate())->getColumn())->setAutoSize(true);
                $col++;

                $sheet->setCellValueExplicitByColumnAndRow($col, $row, (int)$alfa, DataType::TYPE_NUMERIC);
                $sheet->getColumnDimension($sheet->getCell($sheet->getCellByColumnAndRow($col, $row)->getCoordinate())->getColumn())->setAutoSize(true);
                $col++;

                $sheet->setCellValueExplicitByColumnAndRow($col, $row, (int)$total, DataType::TYPE_NUMERIC);
                $sheet->getColumnDimension($sheet->getCell($sheet->getCellByColumnAndRow($col, $row)->getCoordinate())->getColumn())->setAutoSize(true);
                $col++;

                $sheet->setCellValueExplicitByColumnAndRow($col, $row, (string)$hadirPers."%", DataType::TYPE_STRING);
                $sheet->getColumnDimension($sheet->getCell($sheet->getCellByColumnAndRow($col, $row)->getCoordinate())->getColumn())->setAutoSize(true);
                $col++;

                $sheet->setCellValueExplicitByColumnAndRow($col, $row, (string)$hadirIzinPers."%", DataType::TYPE_STRING);
                $sheet->getColumnDimension($sheet->getCell($sheet->getCellByColumnAndRow($col, $row)->getCoordinate())->getColumn())->setAutoSize(true);
                $col++;

                $sheet->getStyleByColumnAndRow($colAwal+2, $row, $col-1, $row)->applyFromArray($style_center);

                $row++;
            }

            $sheet->getStyleByColumnAndRow($colAwal, $rowAwal, $col-1, $row-1)->applyFromArray($style_border);
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
