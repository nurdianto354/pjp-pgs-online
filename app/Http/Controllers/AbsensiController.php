<?php

namespace App\Http\Controllers;

use App\Models\Absensi\Absensi;
use App\Models\Aktivitas\HariLibur;
use App\Models\Aktivitas\Jadwal;
use App\Models\MasterData\Divisi;
use App\Models\MasterData\Kelas;
use App\Models\MasterData\Tanggal;
use App\Models\Murid\Murid;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AbsensiController extends Controller
{
    public function __construct()
    {
        $this->middleware(['permission:absensi']);
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
        $divisiId  = $kelas->divisi_id;

        if($request->has('kelas_id')) {
            $kelas = Kelas::where([['status', true], ['id', $request->kelas_id]])->first();

            $kelasId   = $kelas->id;
            $kelasNama = $kelas->nama;
            $divisiId  = $kelas->divisi_id;
        }

        $listMurid = Murid::select('id', 'nama_panggilan', 'kelas_id', 'jenis_kelamin')->where([['kelas_id', $kelasId], ['status', true]])
            ->orderBy('jenis_kelamin', 'DESC')->orderBy('nama_panggilan', 'ASC')->get();

        $tahun  = $request->has('tahun') ? $request->tahun : Carbon::now()->year;
        $bulan  = $request->has('bulan') ? $request->bulan : Carbon::now()->month;

        $listTahun = Tanggal::where('status', true)->orderBy('tahun', 'DESC')->groupBy('tahun')
                 ->pluck('tahun');
        $listBulan = Tanggal::where([['tahun', $tahun], ['status', true]])->orderBy('bulan', 'ASC')->groupBy('bulan')
                 ->pluck('bulan');

        $jadwal = Jadwal::where([['divisi_id', $divisiId], ['status', true]])
            ->pluck('hari');

        $hariLibur = HariLibur::where([['divisi_id', $divisiId], ['status', true]])
            ->pluck('tanggal');

        $listTanggal = Tanggal::where([['tahun', $tahun], ['bulan', $bulan], ['status', true]])
            ->whereIn('hari', $jadwal)
            ->whereNotIn('tanggal', $hariLibur)
            ->orderBy('tanggal', 'ASC')->get();

        $datas = [];

        $muridIds = $listMurid->pluck('id')->toArray();
        $tanggalTimestamps = $listTanggal->pluck('tanggal')->toArray();

        if (!empty($muridIds) && !empty($tanggalTimestamps)) {
            $allAbsensi = Absensi::select('id', 'murid_id', 'tanggal', 'kehadiran')
                ->where('kelas_id', $kelasId)
                ->whereIn('murid_id', $muridIds)
                ->whereIn('tanggal', $tanggalTimestamps)
                ->get();

            foreach ($allAbsensi as $item) {
                $datas[$item->murid_id][$item->tanggal] = [
                    'id'        => $item->id,
                    'kehadiran' => $item->kehadiran,
                ];
            }
        }

        return view('pages.absensi.index', compact('kelasId', 'kelasNama', 'listKelas', 'listMurid', 'tahun', 'listTahun', 'listBulan',  'bulan','listTanggal', 'datas'));
    }

    public function store(Request $request)
    {
        $success = true;
        $message = "Data has been saved successfully";
        $data    = null;

        DB::beginTransaction();
        try {
            $request->validate([
                'kelas_id'  => 'required|integer',
                'murid_id'  => 'required|integer',
                'tanggal'   => 'required|integer',
                'kehadiran' => 'required|string',
            ]);

            if ($request->id != null && $request->id != '') {
                $data = Absensi::findOrFail($request->id);
            } else {
                $data = Absensi::where([
                    ['kelas_id', $request->kelas_id],
                    ['murid_id', $request->murid_id],
                    ['tanggal', $request->tanggal],
                ])->first() ?: new Absensi();

                if (!$data->exists) {
                    $data->created_at = Carbon::now();
                }
            }

            $data->kelas_id   = $request->kelas_id;
            $data->murid_id   = $request->murid_id;

            $tanggal = Carbon::parse(date("Y-m-d", $request->tanggal));

            $data->tanggal    = $request->tanggal;
            $data->hari       = $tanggal->day;
            $data->bulan      = $tanggal->month;
            $data->tahun      = $tanggal->year;
            $data->kehadiran  = $request->kehadiran;
            $data->updated_at = Carbon::now();
            $data->save();

            DB::commit();
        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollback();

            $success = false;
            $message = $e->validator->errors()->first();
        } catch (\Throwable $e) {
            DB::rollback();
            Log::error('Error saving absensi: ' . $e->getMessage());

            $success = false;
            $message = 'Gagal menyimpan data absensi.';
        }

        return response()->json([
            'data'    => $data,
            'success' => $success,
            'message' => $message
        ]);
    }
}
