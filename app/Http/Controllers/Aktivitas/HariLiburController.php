<?php

namespace App\Http\Controllers\Aktivitas;

use App\Http\Controllers\Controller;
use App\Models\Aktivitas\HariLibur;
use App\Models\MasterData\Divisi;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class HariLiburController extends Controller
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

        $datas = HariLibur::where([['status', true], ['divisi_id', $divisiId]])->orderBy('tanggal', 'DESC')->get();

        return view('pages.aktivitas.hari_libur.index', compact('datas', 'listDivisi', 'divisiId', 'divisiNama'));
    }

    public function create(Request $request)
    {
        $status = "Berhasil";
        $title  = "Data Hari Libur tanggal " . date("d-m-Y", strtotime($request->tanggal));

        $this->validate($request, [
            'divisi_id' => 'required',
            'tanggal'   => 'required',
        ]);

        DB::beginTransaction();
        try {
            $tanggal = Carbon::parse($request->tanggal);
            $timestamp = strtotime($tanggal->format('Y-m-d'));
            $action = "menambahkan";

            // Jika id ada, cari data untuk update
            if ($request->id != null && $request->id != '') {
                $data = HariLibur::findOrFail($request->id);
                $action = "memperbarui";
            } else {
                // Jika id tidak ada, cek apakah data sudah ada berdasarkan divisi_id dan tanggal
                $checkData = HariLibur::where([
                    ['divisi_id', $request->divisi_id],
                    ['tanggal', $timestamp]
                ])->first();

                if (isset($checkData) && $checkData->status == true) {
                    DB::rollBack();
                    $message = "Gagal " . $action . ", karena sudah ada";
                    toast($message, 'error');
                    return back();
                } else {
                    $data = $checkData ?: new HariLibur(); // Jika data tidak ada, buat objek baru
                    if (!$checkData) {
                        $data->created_at = Carbon::now();
                    }
                }
            }

            // Set data yang diterima dari request
            $data->divisi_id  = $request->divisi_id;
            $data->tanggal    = $timestamp;
            $data->hari       = $tanggal->dayOfWeek;
            $data->bulan      = $tanggal->month;
            $data->tahun      = $tanggal->year;
            $data->keterangan = $request->keterangan;
            $data->status     = true;
            $data->updated_at = Carbon::now();

            $data->save();

            DB::commit();

            // Pesan sukses
            $message = $status . " " . $action . " " . $title;
            toast($message, 'success');
            return back();

        } catch (\Exception $e) {
            // Log error jika terjadi exception
            Log::error($e->getMessage());
            DB::rollBack();

            toast('Gagal. Mohon cek kembali', 'error');
            return back();
        }

    }

    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            $data = HariLibur::findOrFail($id);
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
