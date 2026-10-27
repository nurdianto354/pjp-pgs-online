<?php

namespace App\Http\Controllers\Kelompok\MasterData;

use App\Http\Controllers\Controller;
use App\Models\Kelompok\MasterData\Kegiatan;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class KegiatanController extends Controller
{
    public function __construct()
    {
        $this->middleware(['permission:master_data']);
    }

    public function index()
    {
        $datas = Kegiatan::orderBy('lingkup', 'ASC')->orderBy('nama', 'ASC')->get();
        $listLingkup = Kegiatan::listLingkup;

        return view('pages.kelompok.master_data.kegiatan.index', compact('datas', 'listLingkup'));
    }

    public function create(Request $request)
    {
        $this->validate($request, [
            'nama'       => 'required|string|max:255',
            'lingkup'    => 'required|in:1,2,3',
            'keterangan' => 'nullable|string',
            'status'     => 'nullable|boolean',
        ]);

        $action = "menambahkan";
        $title  = "Data Kegiatan " . $request->nama;

        DB::beginTransaction();
        try {
            if (!empty($request->id)) {
                $data = Kegiatan::findOrFail($request->id);
                $action = "memperbarui";
            } else {
                $data = new Kegiatan();
                $data->created_at = Carbon::now();
            }

            $data->nama       = $request->nama;
            $data->lingkup    = (int) $request->lingkup;
            $data->keterangan = $request->keterangan;
            $data->status     = $request->has('status') ? (bool) $request->status : true;
            $data->updated_at = Carbon::now();
            $data->save();

            DB::commit();

            toast("Berhasil " . $action . " " . $title, 'success');
            return back();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Gagal menyimpan Data Kegiatan: ' . $e->getMessage());

            toast('Gagal memproses data. Mohon cek kembali inputan Anda.', 'error');
            return back();
        }
    }

    public function toggleStatus($id)
    {
        DB::beginTransaction();
        try {
            $data = Kegiatan::findOrFail($id);
            $data->status = !$data->status;
            $data->updated_at = Carbon::now();
            $data->save();

            DB::commit();

            $statusText = $data->status ? 'diaktifkan' : 'dinonaktifkan';

            if (request()->ajax() || request()->wantsJson()) {
                return response()->json([
                    'status'     => 'success',
                    'is_active'  => $data->status,
                    'message'    => "Data Kegiatan {$data->nama} berhasil {$statusText}.",
                ]);
            }

            toast("Data Kegiatan {$data->nama} berhasil {$statusText}.", 'success');
            return back();
        } catch (\Throwable $th) {
            DB::rollBack();
            Log::error('Gagal toggle status Kegiatan: ' . $th->getMessage());

            if (request()->ajax() || request()->wantsJson()) {
                return response()->json([
                    'status'     => 'error',
                    'message'    => 'Terjadi kesalahan sistem saat mengubah status.',
                ], 500);
            }

            toast('Terjadi kesalahan saat mengubah status.', 'error');
            return back();
        }
    }
}

