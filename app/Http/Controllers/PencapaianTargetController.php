<?php

namespace App\Http\Controllers;

use App\Models\KurikulumTarget\KurikulumTarget;
use App\Models\KurikulumTarget\KurikulumTargetDetail;
use App\Models\MasterData\Divisi;
use App\Models\MasterData\Kelas;
use App\Models\MasterData\Tanggal;
use App\Models\Murid\Murid;
use App\Models\PencapaianTarget\PencapaianTarget;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PencapaianTargetController extends Controller
{
    public function __construct()
    {
        $this->middleware(['permission:pencapaian_target']);
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

        $listMurid = Murid::select('id', 'nama_panggilan', 'kelas_id', 'jenis_kelamin')->where([['kelas_id', $kelasId], ['status', true]])
            ->orderBy('jenis_kelamin', 'DESC')->orderBy('nama_panggilan', 'ASC')->get();

        $tahun  = $request->has('tahun') ? $request->tahun : Carbon::now()->year;
        $bulan  = $request->has('bulan') ? $request->bulan : Carbon::now()->month;

        $listTahun = Tanggal::where('status', true)->orderBy('tahun', 'DESC')->groupBy('tahun')
                 ->pluck('tahun');
        $listBulan = Tanggal::where([['tahun', $tahun], ['status', true]])->orderBy('bulan', 'ASC')->groupBy('bulan')
                 ->pluck('bulan');

        $id = KurikulumTarget::where([['kelas_id', $kelasId]])->pluck('id')->first();
        $listTargetKurikulum = KurikulumTargetDetail::with('getKarakter', 'getMateri', 'getSatuan')
            ->where('kurikulum_target_id', $id)
            ->orderBy('karakter_id', 'ASC')
            ->orderBy('created_at', 'ASC')
            ->get();

        $listPencapaianTarget = [];

        $muridIds = $listMurid->pluck('id')->toArray();
        if (!empty($muridIds)) {
            $allPencapaian = PencapaianTarget::select('id', 'murid_id', 'kurikulum_target_detail_id', 'target')
                ->where('kelas_id', $kelasId)
                ->whereIn('murid_id', $muridIds)
                ->where('tahun', $tahun)
                ->where('bulan', $bulan)
                ->get();

            foreach ($allPencapaian as $item) {
                $listPencapaianTarget[$item->murid_id][$item->kurikulum_target_detail_id] = [
                    'id'                         => $item->id,
                    'kurikulum_target_detail_id' => $item->kurikulum_target_detail_id,
                    'target'                     => $item->target,
                ];
            }
        }

        return view('pages.pencapaian_target.index', compact('listKelas', 'listMurid', 'listTahun', 'listBulan', 'listTargetKurikulum', 'listPencapaianTarget', 'id', 'kelasId', 'kelasNama', 'tahun', 'bulan'));
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
                'kurikulum_target_detail_id' => 'required|integer',
                'target' => 'required|integer',
                'bulan' => 'required|integer',
                'tahun' => 'required|integer',
            ]);

            if ($request->id != null && $request->id != '') {
                $data = PencapaianTarget::findOrFail($request->id);
            } else {
                $data = PencapaianTarget::where([
                    ['kelas_id', $request->kelas_id],
                    ['murid_id', $request->murid_id],
                    ['kurikulum_target_detail_id', $request->kurikulum_target_detail_id],
                    ['bulan', $request->bulan],
                    ['tahun', $request->tahun],
                ])->first() ?: new PencapaianTarget();

                if (!$data->exists) {
                    $data->created_at = Carbon::now();
                }
            }

            $data->kelas_id                   = $request->kelas_id;
            $data->murid_id                   = $request->murid_id;
            $data->kurikulum_target_detail_id = $request->kurikulum_target_detail_id;
            $data->target                     = $request->target;
            $data->bulan                      = $request->bulan;
            $data->tahun                      = $request->tahun;
            $data->updated_at                 = Carbon::now();
            $data->save();

            DB::commit();
        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollback();

            $success = false;
            $message = $e->validator->errors()->first();
        } catch (\Throwable $e) {
            DB::rollback();
            Log::error('Error saving pencapaian target: ' . $e->getMessage());

            $success = false;
            $message = 'Gagal menyimpan data pencapaian target.';
        }

        return response()->json([
            'data'    => $data,
            'success' => $success,
            'message' => $message
        ]);
    }
}
