<?php

namespace App\Http\Controllers\Kelompok\MasterData;

use App\Http\Controllers\Controller;
use App\Models\Kelompok\MasterData\Lokasi;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class LokasiController extends Controller
{
    public function __construct()
    {
        $this->middleware(['permission:master_data']);
    }

    public function index()
    {
        $datas = Lokasi::orderBy('nama', 'ASC')->get();

        return view('pages.kelompok.master_data.lokasi.index', compact('datas'));
    }

    public function create(Request $request)
    {
        $this->validate($request, [
            'nama'      => 'required|string|max:255',
            'alamat'    => 'nullable|string|max:255',
            'latitude'  => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'radius'    => 'nullable|integer|min:1|max:50000',
            'status'    => 'nullable|boolean',
        ]);

        $action = "menambahkan";
        $title  = "Data Lokasi " . $request->nama;

        DB::beginTransaction();
        try {
            if (!empty($request->id)) {
                $data = Lokasi::findOrFail($request->id);
                $action = "memperbarui";
            } else {
                $data = new Lokasi();
                $data->created_at = Carbon::now();
            }

            $data->nama       = $request->nama;
            $data->alamat     = $request->alamat;
            $data->latitude   = $request->filled('latitude') ? $request->latitude : null;
            $data->longitude  = $request->filled('longitude') ? $request->longitude : null;
            $data->radius     = $request->filled('radius') ? (int) $request->radius : 100;
            $data->status     = $request->has('status') ? (bool) $request->status : true;
            $data->updated_at = Carbon::now();
            $data->save();

            DB::commit();

            toast("Berhasil " . $action . " " . $title, 'success');
            return back();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Gagal menyimpan Data Lokasi: ' . $e->getMessage());

            toast('Gagal memproses data. Mohon periksa kembali inputan Anda.', 'error');
            return back();
        }
    }

    public function toggleStatus($id)
    {
        DB::beginTransaction();
        try {
            $data = Lokasi::findOrFail($id);
            $data->status = !$data->status;
            $data->updated_at = Carbon::now();
            $data->save();

            DB::commit();

            $statusText = $data->status ? 'diaktifkan' : 'dinonaktifkan';

            if (request()->ajax() || request()->wantsJson()) {
                return response()->json([
                    'status'    => 'success',
                    'is_active' => $data->status,
                    'message'   => "Data Lokasi {$data->nama} berhasil {$statusText}.",
                ]);
            }

            toast("Data Lokasi {$data->nama} berhasil {$statusText}.", 'success');
            return back();
        } catch (\Throwable $th) {
            DB::rollBack();
            Log::error('Gagal toggle status Lokasi: ' . $th->getMessage());

            if (request()->ajax() || request()->wantsJson()) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'Terjadi kesalahan sistem saat mengubah status.',
                ], 500);
            }

            toast('Terjadi kesalahan saat mengubah status.', 'error');
            return back();
        }
    }
}
