<?php

namespace App\Http\Controllers\Kelompok;

use App\Http\Controllers\Controller;
use App\Models\Kelompok\Absensi;
use App\Models\Kelompok\Jadwal;
use App\Models\Kelompok\MasterData\Jamaah;
use App\Models\Kelompok\MasterData\Kelompok;
use App\Models\MasterData\Divisi;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AbsensiKelompokController extends Controller
{
    /**
     * Hitung jarak dua titik koordinat bumi dalam satuan Meter (Haversine formula).
     */
    private function calculateDistanceMeter($lat1, $lon1, $lat2, $lon2)
    {
        $earthRadius = 6371000; // Radius bumi dalam meter

        $latFrom = deg2rad($lat1);
        $lonFrom = deg2rad($lon1);
        $latTo   = deg2rad($lat2);
        $lonTo   = deg2rad($lon2);

        $latDelta = $latTo - $latFrom;
        $lonDelta = $lonTo - $lonFrom;

        $angle = 2 * asin(sqrt(pow(sin($latDelta / 2), 2) +
            cos($latFrom) * cos($latTo) * pow(sin($lonDelta / 2), 2)));

        return round($angle * $earthRadius);
    }

    // =========================================================================
    // BAGIAN 1: FORM PRESENSI PUBLIK (AKSES TANPA LOGIN)
    // =========================================================================

    /**
     * Menampilkan form presensi publik untuk jadwal tertentu (bebas akses / tanpa login).
     */
    public function publicForm($jadwal_id)
    {
        $jadwal = Jadwal::with(['getKegiatan', 'getLokasi'])->findOrFail($jadwal_id);

        if (!$jadwal->status) {
            return view('pages.kelompok.absensi.public_inactive', compact('jadwal'));
        }

        return view('pages.kelompok.absensi.public_form', compact('jadwal'));
    }

    /**
     * Mengambil daftar jamaah berdasarkan filter jenis kelamin via AJAX (publik).
     * Sesuai kebutuhan: jamaah yang SUDAH absen pada jadwal ini TIDAK ditampilkan lagi.
     */
    public function getJamaahList(Request $request, $jadwal_id)
    {
        $jadwal = Jadwal::findOrFail($jadwal_id);

        $query = Jamaah::with(['getDivisi'])
            ->where('status', true);

        // Filter Jenis Kelamin (1 = Laki-laki, 0 = Perempuan)
        if ($request->has('jenis_kelamin') && $request->jenis_kelamin !== '' && $request->jenis_kelamin !== null) {
            $query->where('jenis_kelamin', (int) $request->jenis_kelamin);
        }

        // Jangan tampilkan jamaah yang SUDAH melakukan presensi untuk jadwal ini
        $alreadyAbsenIds = Absensi::where('jadwal_id', $jadwal->id)
            ->pluck('jamaah_id')
            ->toArray();

        if (!empty($alreadyAbsenIds)) {
            $query->whereNotIn('id', $alreadyAbsenIds);
        }

        $jamaahs = $query->orderBy('nama_lengkap', 'ASC')->get();

        $data = $jamaahs->map(function ($j) {
            $divisiNama = $j->getDivisi ? $j->getDivisi->nama : 'Kelompok';

            return [
                'id'            => $j->id,
                'nama_lengkap'  => $j->nama_lengkap,
                'jenis_kelamin' => $j->jenis_kelamin == 1 ? 'Laki-laki' : 'Perempuan',
                'divisi'        => $divisiNama,
                'sudah_absen'   => false,
                'status_absen'  => null,
            ];
        });

        return response()->json([
            'status' => 'success',
            'data'   => $data,
        ]);
    }

    /**
     * Memproses penyimpanan presensi dari form publik.
     */
    public function publicStore(Request $request, $jadwal_id)
    {
        $jadwal = Jadwal::with('getLokasi')->findOrFail($jadwal_id);

        if (!$jadwal->status) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Jadwal kegiatan ini sedang nonaktif.',
            ], 422);
        }

        $this->validate($request, [
            'jamaah_id'  => 'required|integer|exists:kelompok_m_jamaah,id',
            'status'     => 'required|in:hadir,sdc,izin',
            'keterangan' => 'nullable|string|max:1000',
            'latitude'   => 'nullable|numeric',
            'longitude'  => 'nullable|numeric',
        ], [
            'jamaah_id.required' => 'Silakan pilih nama jamaah terlebih dahulu!',
            'status.required'    => 'Silakan pilih status kehadiran (Hadir, SDC, atau Izin)!',
        ]);

        $status = strtolower($request->status);
        $keterangan = trim($request->keterangan);

        // KETENTUAN 1: SDC dan Izin WAJIB mengisi keterangan
        if (in_array($status, ['sdc', 'izin']) && empty($keterangan)) {
            $labelStatus = strtoupper($status);
            return response()->json([
                'status'  => 'error',
                'message' => "Untuk status kehadiran {$labelStatus}, kolom Keterangan / Alasan WAJIB diisi!",
            ], 422);
        }

        $matchedLokasiId = null;
        $recordedDistance = null;
        $isInRadius = false;

        // KETENTUAN 2: Hanya status 'hadir' yang dicek GPS Geofencing
        if ($status == 'hadir') {
            if (!$request->filled('latitude') || !$request->filled('longitude')) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'Lokasi GPS Anda tidak terdeteksi. Harap aktifkan GPS / Izin Lokasi di browser HP Anda untuk presensi Hadir!',
                ], 422);
            }

            $userLat = (float) $request->latitude;
            $userLng = (float) $request->longitude;

            $validLocations = $jadwal->getLokasi->filter(function ($loc) {
                return !empty($loc->latitude) && !empty($loc->longitude);
            });

            if ($validLocations->isNotEmpty()) {
                $minDistance = null;
                $closestLocation = null;

                foreach ($validLocations as $loc) {
                    $dist = $this->calculateDistanceMeter($userLat, $userLng, (float) $loc->latitude, (float) $loc->longitude);
                    $radiusLimit = $loc->radius ?: 100;

                    if ($minDistance === null || $dist < $minDistance) {
                        $minDistance = $dist;
                        $closestLocation = $loc;
                    }

                    // Jika berada di dalam radius salah satu titik lokasi (misal tipe turba multi-lokasi)
                    if ($dist <= $radiusLimit) {
                        $matchedLokasiId = $loc->id;
                        $recordedDistance = $dist;
                        $isInRadius = true;
                        break;
                    }
                }

                if (!$isInRadius) {
                    $closestName = $closestLocation ? $closestLocation->nama : 'Lokasi Kegiatan';
                    $closestRadius = $closestLocation ? ($closestLocation->radius ?: 100) : 100;

                    return response()->json([
                        'status'   => 'error',
                        'message'  => "Presensi Hadir Ditolak: Anda berada di luar radius lokasi kegiatan! Jarak terdekat ke {$closestName} adalah {$minDistance} meter (Maksimal diizinkan: {$closestRadius} meter).",
                        'distance' => $minDistance,
                    ], 422);
                }
            } else {
                // Jika lokasi pada jadwal belum diset koordinat lat/long-nya, loloskan
                $isInRadius = true;
            }
        }

        DB::beginTransaction();
        try {
            $absensi = Absensi::updateOrCreate(
                [
                    'jadwal_id' => $jadwal->id,
                    'jamaah_id' => $request->jamaah_id,
                ],
                [
                    'status'         => $status,
                    'keterangan'     => $keterangan ?: null,
                    'latitude'       => $request->filled('latitude') ? $request->latitude : null,
                    'longitude'      => $request->filled('longitude') ? $request->longitude : null,
                    'lokasi_id'      => $matchedLokasiId,
                    'jarak_meter'    => $recordedDistance,
                    'is_in_radius'   => $isInRadius,
                    'waktu_presensi' => Carbon::now(),
                    'updated_at'     => Carbon::now(),
                ]
            );

            DB::commit();

            $jamaah = Jamaah::find($request->jamaah_id);
            $namaJamaah = $jamaah ? $jamaah->nama_lengkap : 'Jamaah';
            $statusLabel = strtoupper($status);

            return response()->json([
                'status'  => 'success',
                'message' => "Alhamdulillah! Presensi {$statusLabel} untuk {$namaJamaah} berhasil dicatat.",
                'data'    => $absensi,
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            Log::error('Gagal menyimpan presensi publik: ' . $th->getMessage());

            return response()->json([
                'status'  => 'error',
                'message' => 'Terjadi kesalahan sistem saat menyimpan data presensi.',
            ], 500);
        }
    }

    /**
     * Halaman rekapitulasi presensi publik (bebas akses / tanpa login).
     * Dapat difilter berdasarkan jenis kelamin (Semua, Laki-laki, Perempuan).
     */
    public function publicRekap(Request $request, $jadwal_id)
    {
        $jadwal = Jadwal::with(['getKegiatan', 'getLokasi'])->findOrFail($jadwal_id);

        $filterJk = $request->query('jk'); // null, '1', atau '0'

        $query = Absensi::with(['getJamaah.getDivisi', 'getLokasi'])
            ->where('jadwal_id', $jadwal->id);

        if ($filterJk !== null && $filterJk !== '') {
            $query->whereHas('getJamaah', function ($q) use ($filterJk) {
                $q->where('jenis_kelamin', (int) $filterJk);
            });
        }

        $presensis = $query->orderBy('waktu_presensi', 'DESC')->get();

        // Hitung statistik untuk tampilan rekap
        $countHadir = $presensis->where('status', 'hadir')->count();
        $countSDC   = $presensis->where('status', 'sdc')->count();
        $countIzin  = $presensis->where('status', 'izin')->count();
        $totalPresensi = $presensis->count();

        // Ambil juga total jamaah master aktif (sesuai filter jenis kelamin jika ada)
        $masterQuery = Jamaah::where('status', true);
        if ($filterJk !== null && $filterJk !== '') {
            $masterQuery->where('jenis_kelamin', (int) $filterJk);
        }
        $totalJamaahMaster = $masterQuery->count();
        $totalBelumHadir = max(0, $totalJamaahMaster - $totalPresensi);

        // Ambil ringkasan per gender untuk info badge
        $totalLakiSudah = Absensi::where('jadwal_id', $jadwal->id)
            ->whereHas('getJamaah', function ($q) { $q->where('jenis_kelamin', 1); })
            ->count();
        $totalPerempuanSudah = Absensi::where('jadwal_id', $jadwal->id)
            ->whereHas('getJamaah', function ($q) { $q->where('jenis_kelamin', 0); })
            ->count();

        return view('pages.kelompok.absensi.public_rekap', compact(
            'jadwal',
            'presensis',
            'countHadir',
            'countSDC',
            'countIzin',
            'totalPresensi',
            'totalJamaahMaster',
            'totalBelumHadir',
            'totalLakiSudah',
            'totalPerempuanSudah',
            'filterJk'
        ));
    }

    // =========================================================================
    // BAGIAN 2: DASHBOARD ADMIN / PENGURUS (DENGAN LOGIN)
    // =========================================================================

    public function __construct()
    {
        // Hanya rute dashboard yang diproteksi auth & permission
        $this->middleware(['permission:master_data'])->except(['publicForm', 'getJamaahList', 'publicStore', 'publicRekap']);
    }

    /**
     * Halaman index monitoring absensi kelompok per sesi jadwal.
     */
    public function index()
    {
        $jadwals = Jadwal::with(['getKegiatan', 'getLokasi'])
            ->withCount([
                'getLokasi',
            ])
            ->orderBy('tanggal', 'DESC')
            ->orderBy('jam_mulai', 'ASC')
            ->get();

        // Hitung statistik Hadir, SDC, Izin per jadwal
        $stats = Absensi::select('jadwal_id', 'status', DB::raw('count(*) as total'))
            ->groupBy('jadwal_id', 'status')
            ->get()
            ->groupBy('jadwal_id');

        return view('pages.kelompok.absensi.index', compact('jadwals', 'stats'));
    }

    /**
     * Halaman detail rekapitulasi kehadiran jamaah pada satu sesi jadwal.
     */
    public function detail($jadwal_id)
    {
        $jadwal = Jadwal::with(['getKegiatan', 'getLokasi'])->findOrFail($jadwal_id);

        $presensis = Absensi::with(['getJamaah.getDivisi', 'getLokasi'])
            ->where('jadwal_id', $jadwal->id)
            ->orderBy('waktu_presensi', 'DESC')
            ->get();

        $listDivisi = Divisi::whereIn('nama', ['Remaja', 'Pranikah'])->get();
        $listKelompok = Kelompok::where('status', true)->orderBy('nama', 'ASC')->get();
        $listJamaahAll = Jamaah::where('status', true)->orderBy('nama_lengkap', 'ASC')->get();

        $countHadir = $presensis->where('status', 'hadir')->count();
        $countSDC   = $presensis->where('status', 'sdc')->count();
        $countIzin  = $presensis->where('status', 'izin')->count();

        return view('pages.kelompok.absensi.detail', compact(
            'jadwal',
            'presensis',
            'countHadir',
            'countSDC',
            'countIzin',
            'listDivisi',
            'listKelompok',
            'listJamaahAll'
        ));
    }

    /**
     * Hapus satu data presensi oleh admin.
     */
    public function destroy($id)
    {
        try {
            $absensi = Absensi::findOrFail($id);
            $absensi->delete();

            toast('Data presensi berhasil dihapus.', 'success');
            return back();
        } catch (\Throwable $th) {
            Log::error('Gagal menghapus presensi: ' . $th->getMessage());
            toast('Gagal menghapus data presensi.', 'error');
            return back();
        }
    }
}

