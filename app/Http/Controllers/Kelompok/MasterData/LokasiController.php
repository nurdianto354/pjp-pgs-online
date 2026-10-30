<?php

namespace App\Http\Controllers\Kelompok\MasterData;

use App\Http\Controllers\Controller;
use App\Models\Kelompok\MasterData\Kelompok;
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
        $listKelompok = Kelompok::where('status', true)->orderBy('nama', 'ASC')->get();
        $datas = Lokasi::with('getKelompok')->orderBy('nama', 'ASC')->get();

        return view('pages.kelompok.master_data.lokasi.index', compact('datas', 'listKelompok'));
    }

    public function create(Request $request)
    {
        $this->validate($request, [
            'nama'        => 'required|string|max:255',
            'kelompok_id' => 'nullable|integer',
            'alamat'      => 'nullable|string|max:1000',
            'latitude'    => 'nullable|numeric|between:-90,90',
            'longitude'   => 'nullable|numeric|between:-180,180',
            'radius'      => 'nullable|integer|min:1|max:50000',
            'status'      => 'nullable|boolean',
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

            $data->nama        = $request->nama;
            $data->kelompok_id = $request->filled('kelompok_id') ? (int) $request->kelompok_id : null;
            $data->alamat      = $request->alamat;
            $data->latitude    = $request->filled('latitude') ? $request->latitude : null;
            $data->longitude   = $request->filled('longitude') ? $request->longitude : null;
            $data->radius      = $request->filled('radius') ? (int) $request->radius : 100;
            $data->status      = $request->has('status') ? (bool) $request->status : true;
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

    public function parseMapsUrl(Request $request)
    {
        $this->validate($request, [
            'url' => 'required|string',
        ]);

        $input = trim($request->url);

        // 1. Raw coordinates: "-6.175392, 106.827153"
        if (preg_match('/^(-?\d{1,2}(?:\.\d+)?)\s*,\s*(-?\d{1,3}(?:\.\d+)?)$/', $input, $matches)) {
            return response()->json([
                'status'    => 'success',
                'latitude'  => (float) $matches[1],
                'longitude' => (float) $matches[2],
            ]);
        }

        // 2. Short links (maps.app.goo.gl or goo.gl/maps) -> resolve redirect
        if (preg_match('/(?:maps\.app\.goo\.gl|goo\.gl\/maps)/i', $input)) {
            try {
                $ch = curl_init($input);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
                curl_setopt($ch, CURLOPT_MAXREDIRS, 5);
                curl_setopt($ch, CURLOPT_NOBODY, true);
                curl_setopt($ch, CURLOPT_TIMEOUT, 6);
                curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)');
                curl_exec($ch);
                $finalUrl = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
                curl_close($ch);
                if ($finalUrl) {
                    $input = $finalUrl;
                }
            } catch (\Throwable $e) {
                Log::warning('Failed resolving short Google Maps URL: ' . $e->getMessage());
            }
        }

        // 3. Match /@lat,lng
        if (preg_match('/@(-?\d+\.\d+),(-?\d+\.\d+)/', $input, $matches)) {
            return response()->json([
                'status'    => 'success',
                'latitude'  => (float) $matches[1],
                'longitude' => (float) $matches[2],
            ]);
        }

        // 4. Match !3d<lat>!4d<lng>
        if (preg_match('/!3d(-?\d+\.\d+)!4d(-?\d+\.\d+)/', $input, $matches)) {
            return response()->json([
                'status'    => 'success',
                'latitude'  => (float) $matches[1],
                'longitude' => (float) $matches[2],
            ]);
        }

        // 5. Match query params ?q=lat,lng or ?query=lat,lng or ?ll=lat,lng or destination=lat,lng
        if (preg_match('/[?&](?:q|query|ll|center|destination)=(-?\d+\.\d+),(-?\d+\.\d+)/', $input, $matches)) {
            return response()->json([
                'status'    => 'success',
                'latitude'  => (float) $matches[1],
                'longitude' => (float) $matches[2],
            ]);
        }

        return response()->json([
            'status'  => 'error',
            'message' => 'Titik koordinat tidak ditemukan di dalam link atau teks yang dimasukkan. Pastikan link Google Maps valid.',
        ], 422);
    }
}
