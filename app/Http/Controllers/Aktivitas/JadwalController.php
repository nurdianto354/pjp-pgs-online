<?php

namespace App\Http\Controllers\Aktivitas;

use App\Http\Controllers\Controller;
use App\Models\Aktivitas\Jadwal;
use App\Models\MasterData\Divisi;
use App\Models\MasterData\Tanggal;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class JadwalController extends Controller
{
    public function __construct()
    {
        $this->middleware(['permission:aktivitas']);
    }

    public function index(Request $request)
    {
        $user = Auth::user();
        $roles = $user->getRoleNames()->toArray();

        if (array_intersect($roles, ['paud', 'caberawit', 'praremaja', 'remaja', 'pranikah'])) {
            $divisiIds  = [];
            $divisiNama = "";
            // Loop untuk mengambil divisi dan kelas berdasarkan peran
            foreach ($roles as $index => $role) {
                if ($index == 0) {
                    $divisiNama = ucfirst(strtolower($role));
                }

                $divisi = Divisi::select('id')
                    ->where([['nama', ucfirst(strtolower($role))], ['status', true]])
                    ->first();

                // Pastikan divisi ditemukan sebelum melanjutkan
                if ($divisi) {
                    $divisiIds[] = $divisi->id; // Simpan ID Divisi
                }
            }

            $divisi = Divisi::where([['status', true], ['nama', $divisiNama]])->first();
            $listDivisi = Divisi::whereIn('id', $divisiIds)->where('status', true)->orderBy('id', 'ASC')->get();
        } else {
            $divisi = Divisi::where([['status', true], ['nama', 'Paud']])->first();
            $listDivisi = Divisi::where('status', true)->orderBy('id', 'ASC')->get();
        }

        $divisiId   = $divisi->id;
        $divisiNama = $divisi->nama;

        if($request->has('divisi_id')) {
            $divisi = Divisi::where([['status', true], ['id', $request->divisi_id]])->first();

            $divisiId   = $divisi->id;
            $divisiNama = $divisi->nama;
        }

        $datas = Jadwal::where([['status', true], ['divisi_id', $divisiId]])->orderBy('hari', 'ASC')->get();

        return view('pages.aktivitas.jadwal.index', compact('datas', 'listDivisi', 'divisiId', 'divisiNama'));
    }

    public function create(Request $request)
    {
        $status = "Berhasil";
        $title  = "Data Jadwal Hari " . Tanggal::listDay[$request->hari] . " Waktu " . $request->waktu_mulai . " : " . $request->waktu_selesai;

        $this->validate($request, [
            'divisi_id' => 'required|integer',
            'hari' => 'required|integer',
        ]);

        DB::beginTransaction();
        try {
            $action = "menambahkan";
            $data = new Jadwal();
            $data->created_at = Carbon::now();

            if ($request->id != null && $request->id != '') {
                $action = "memperbarui";
                $data = Jadwal::findOrFail($request->id);
            } else {
                $checkData = Jadwal::where([['divisi_id', $request->divisi_id], ['hari', $request->hari]])->first();

                if (isset($checkData) && $checkData->status == true) {
                    DB::rollBack();

                    $message = "Gagal" . " " . $action . " " . $title . ", karena sudah ada";

                    toast($message, 'error');
                    return back();
                } else {
                    $data = $checkData ?: new Jadwal();
                    $data->created_at = Carbon::now();
                }
            }

            $data->divisi_id     = $request->divisi_id;
            $data->hari          = $request->hari;
            $data->waktu_mulai   = $request->waktu_mulai;
            $data->waktu_selesai = $request->waktu_selesai;
            $data->status        = true;
            $data->updated_at    = Carbon::now();
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
            $data = Jadwal::findOrFail($id);
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
}
