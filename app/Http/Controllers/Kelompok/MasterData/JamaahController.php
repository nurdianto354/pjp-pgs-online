<?php

namespace App\Http\Controllers\Kelompok\MasterData;

use App\Http\Controllers\Controller;
use App\Models\Kelompok\MasterData\Jamaah;
use App\Models\Kelompok\MasterData\Kelompok;
use App\Models\MasterData\Divisi;
use App\Models\Murid\Murid;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class JamaahController extends Controller
{
    public function __construct()
    {
        $this->middleware(['permission:master_data']);
    }

    public function index()
    {
        // Ambil divisi Remaja dan Pranikah
        $divisiRemajaPranikah = Divisi::whereIn('nama', ['Remaja', 'Pranikah'])->pluck('id')->toArray();
        if (empty($divisiRemajaPranikah)) {
            $divisiRemajaPranikah = [4, 5];
        }

        // Ambil list murid berstatus aktif yang divisinya Remaja atau Pranikah
        $listMurid = Murid::with('getDivisi')
            ->whereIn('divisi_id', $divisiRemajaPranikah)
            ->where('status', true)
            ->orderBy('nama_lengkap', 'ASC')
            ->get();

        // Ambil semua data jamaah beserta relasi divisi dan murid
        $datas = Jamaah::with(['getDivisi', 'getMurid'])
            ->orderBy('nama_lengkap', 'ASC')
            ->get();

        return view('pages.kelompok.master_data.jamaah.index', compact('datas', 'listMurid'));
    }

    public function create(Request $request)
    {
        $this->validate($request, [
            'nama_lengkap'  => 'required|string|max:255',
            'jenis_kelamin' => 'required|in:0,1',
            'murid_id'      => 'nullable|integer',
            'status'        => 'nullable|boolean',
        ]);

        $action = "menambahkan";
        $title  = "Data Jamaah " . $request->nama_lengkap;

        DB::beginTransaction();
        try {
            if (!empty($request->id)) {
                $data = Jamaah::findOrFail($request->id);
                $action = "memperbarui";
            } else {
                $data = new Jamaah();
                $data->created_at = Carbon::now();
            }

            $data->nama_lengkap  = $request->nama_lengkap;
            $data->jenis_kelamin = (int) $request->jenis_kelamin;
            $data->status        = $request->has('status') ? (bool) $request->status : true;

            // Jika dipilih dari murid
            if ($request->filled('murid_id')) {
                $murid = Murid::find($request->murid_id);
                if ($murid) {
                    $data->murid_id  = $murid->id;
                    $data->divisi_id = $murid->divisi_id;
                } else {
                    $data->murid_id  = null;
                    $data->divisi_id = null;
                }
            } else {
                // Jamaah umum (non-murid), divisi_id dikosongkan (null)
                $data->murid_id  = null;
                $data->divisi_id = null;
            }

            $data->updated_at = Carbon::now();
            $data->save();

            DB::commit();

            toast("Berhasil " . $action . " " . $title, 'success');
            return back();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Gagal menyimpan Data Jamaah: ' . $e->getMessage());

            toast('Gagal memproses data: ' . $e->getMessage(), 'error');
            return back();
        }
    }

    public function toggleStatus($id)
    {
        DB::beginTransaction();
        try {
            $data = Jamaah::findOrFail($id);
            $data->status = !$data->status;
            $data->updated_at = Carbon::now();
            $data->save();

            DB::commit();

            $statusText = $data->status ? 'diaktifkan' : 'dinonaktifkan';

            if (request()->ajax() || request()->wantsJson()) {
                return response()->json([
                    'status'    => 'success',
                    'is_active' => $data->status,
                    'message'   => "Data Jamaah {$data->nama_lengkap} berhasil {$statusText}.",
                ]);
            }

            toast("Data Jamaah {$data->nama_lengkap} berhasil {$statusText}.", 'success');
            return back();
        } catch (\Throwable $th) {
            DB::rollBack();
            Log::error('Gagal mengubah status Data Jamaah: ' . $th->getMessage());

            if (request()->ajax() || request()->wantsJson()) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'Gagal mengubah status data jamaah.',
                ], 500);
            }

            toast('Gagal mengubah status data jamaah.', 'error');
            return back();
        }
    }

    public function sync(Request $request)
    {
        DB::beginTransaction();
        try {
            $divisiRemajaPranikah = Divisi::whereIn('nama', ['Remaja', 'Pranikah'])->pluck('id')->toArray();
            if (empty($divisiRemajaPranikah)) {
                $divisiRemajaPranikah = [4, 5];
            }

            // Ambil semua murid di divisi Remaja dan Pranikah
            $murids = Murid::whereIn('divisi_id', $divisiRemajaPranikah)->get();

            $createdCount = 0;
            $updatedCount = 0;

            foreach ($murids as $murid) {
                $jamaah = Jamaah::where('murid_id', $murid->id)->first();

                if ($jamaah) {
                    // Update data yang sudah ada
                    $jamaah->nama_lengkap  = $murid->nama_lengkap;
                    $jamaah->jenis_kelamin = (int) $murid->jenis_kelamin;
                    $jamaah->divisi_id     = $murid->divisi_id;
                    $jamaah->status        = (bool) $murid->status;
                    $jamaah->updated_at    = Carbon::now();
                    $jamaah->save();

                    $updatedCount++;
                } else {
                    // Tambahkan data baru jika belum ada
                    Jamaah::create([
                        'murid_id'      => $murid->id,
                        'divisi_id'     => $murid->divisi_id,
                        'nama_lengkap'  => $murid->nama_lengkap,
                        'jenis_kelamin' => (int) $murid->jenis_kelamin,
                        'status'        => (bool) $murid->status,
                        'created_at'    => Carbon::now(),
                        'updated_at'    => Carbon::now(),
                    ]);

                    $createdCount++;
                }
            }

            DB::commit();

            $message = "Sinkronisasi berhasil! {$createdCount} data baru ditambahkan dan {$updatedCount} data diperbarui.";

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'status'  => 'success',
                    'message' => $message,
                    'created' => $createdCount,
                    'updated' => $updatedCount,
                    'total'   => $murids->count(),
                ]);
            }

            toast($message, 'success');
            return back();
        } catch (\Throwable $th) {
            DB::rollBack();
            Log::error('Gagal sinkronisasi data jamaah dari murid: ' . $th->getMessage());

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'Gagal melakukan sinkronisasi data: ' . $th->getMessage(),
                ], 500);
            }

            toast('Gagal melakukan sinkronisasi data.', 'error');
            return back();
        }
    }
}

