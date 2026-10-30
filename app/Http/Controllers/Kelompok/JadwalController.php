<?php

namespace App\Http\Controllers\Kelompok;

use App\Http\Controllers\Controller;
use App\Models\Kelompok\Jadwal;
use App\Models\Kelompok\MasterData\Kegiatan;
use App\Models\Kelompok\MasterData\Kelompok;
use App\Models\Kelompok\MasterData\Lokasi;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class JadwalController extends Controller
{
    public function __construct()
    {
        $this->middleware(['permission:master_data']);
    }

    public function index()
    {
        $listKegiatan = Kegiatan::where('status', true)->orderBy('nama', 'ASC')->get();
        $listLokasi   = Lokasi::where('status', true)->orderBy('nama', 'ASC')->get();

        $datas = Jadwal::with(['getKegiatan', 'getLokasi'])
            ->orderBy('tanggal', 'DESC')
            ->orderBy('jam_mulai', 'ASC')
            ->get();

        return view('pages.kelompok.jadwal.index', compact('datas', 'listKegiatan', 'listLokasi'));
    }

    public function create(Request $request)
    {
        $this->validate($request, [
            'kegiatan_id'   => 'required|integer',
            'tipe'          => 'required|in:turba,total',
            'lokasi_ids'    => 'required|array|min:1',
            'lokasi_ids.*'  => 'integer',
            'tanggal'       => 'required|date',
            'jam_mulai'     => 'required',
            'jam_selesai'   => 'required',
            'keterangan'    => 'nullable|string|max:1000',
            'status'        => 'nullable|boolean',
        ], [
            'kegiatan_id.required'  => 'Harap pilih kegiatan!',
            'tipe.required'         => 'Harap tentukan tipe kegiatan (Turba / Total)!',
            'lokasi_ids.required'   => 'Harap pilih minimal 1 lokasi!',
            'lokasi_ids.min'        => 'Harap pilih minimal 1 lokasi kegiatan!',
            'tanggal.required'      => 'Tanggal pelaksanaan wajib diisi!',
            'jam_mulai.required'    => 'Jam mulai wajib diisi!',
            'jam_selesai.required'  => 'Jam selesai wajib diisi!',
        ]);

        $action = "menambahkan";
        $title  = "Jadwal Kegiatan";

        DB::beginTransaction();
        try {
            if (!empty($request->id)) {
                $jadwal = Jadwal::findOrFail($request->id);
                $action = "memperbarui";
            } else {
                $jadwal = new Jadwal();
                $jadwal->created_at = Carbon::now();
            }

            $jadwal->kegiatan_id   = (int) $request->kegiatan_id;
            $jadwal->tipe          = $request->tipe;
            $jadwal->kelompok_id   = null;
            $jadwal->tanggal       = $request->tanggal;
            $jadwal->jam_mulai     = $request->jam_mulai;
            $jadwal->jam_selesai   = $request->jam_selesai;
            $jadwal->keterangan    = $request->keterangan;
            $jadwal->status        = $request->has('status') ? (bool) $request->status : true;
            $jadwal->updated_at    = Carbon::now();
            $jadwal->save();

            // Sinkronisasi Many-to-Many Multi Lokasi
            $jadwal->getLokasi()->sync($request->lokasi_ids);

            DB::commit();

            toast("Berhasil " . $action . " " . $title, 'success');
            return back();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Gagal menyimpan Jadwal Kegiatan: ' . $e->getMessage());

            toast('Gagal memproses data: ' . $e->getMessage(), 'error');
            return back();
        }
    }

    public function toggleStatus($id)
    {
        DB::beginTransaction();
        try {
            $jadwal = Jadwal::with('getKegiatan')->findOrFail($id);
            $jadwal->status = !$jadwal->status;
            $jadwal->updated_at = Carbon::now();
            $jadwal->save();

            DB::commit();

            $statusText = $jadwal->status ? 'diaktifkan' : 'dinonaktifkan';
            $kegiatanNama = $jadwal->getKegiatan ? $jadwal->getKegiatan->nama : 'Kegiatan';

            if (request()->ajax() || request()->wantsJson()) {
                return response()->json([
                    'status'    => 'success',
                    'is_active' => $jadwal->status,
                    'message'   => "Jadwal {$kegiatanNama} berhasil {$statusText}.",
                ]);
            }

            toast("Jadwal {$kegiatanNama} berhasil {$statusText}.", 'success');
            return back();
        } catch (\Throwable $th) {
            DB::rollBack();
            Log::error('Gagal mengubah status Jadwal: ' . $th->getMessage());

            if (request()->ajax() || request()->wantsJson()) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'Gagal mengubah status jadwal kegiatan.',
                ], 500);
            }

            toast('Gagal mengubah status jadwal kegiatan.', 'error');
            return back();
        }
    }
}

