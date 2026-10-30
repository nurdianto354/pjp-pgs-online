<?php

namespace App\Http\Controllers;

use App\Models\Kelompok\Absensi as KelompokAbsensi;
use App\Models\Kelompok\Jadwal as KelompokJadwal;
use App\Models\Kelompok\MasterData\Jamaah as KelompokJamaah;
use App\Models\MasterData\Divisi;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $user = Auth::user();
        $roles = $user->getRoleNames()->toArray();

        // 1. Filter Divisi Berdasarkan Role User
        if (array_intersect($roles, ['paud', 'caberawit', 'praremaja', 'remaja', 'pranikah'])) {
            $divisiIds = [];

            foreach ($roles as $role) {
                $divisi = Divisi::select('id')
                    ->where([['nama', ucfirst(strtolower($role))], ['status', true]])
                    ->first();

                if ($divisi) {
                    $divisiIds[] = $divisi->id;
                }
            }

            $listDivisi = Divisi::with('listKelas.listMurid')->where([['status', true]])->whereIn('id', $divisiIds)->get();
        } else {
            $listDivisi = Divisi::with('listKelas.listMurid')->where([['status', true]])->get();
        }

        // 2. Statistik Generus / Murid
        $totalMurid = 0;
        $totalMuridLaki = 0;
        $totalMuridPerempuan = 0;
        $divisiLabels = [];
        $divisiTotals = [];
        $divisiLakiTotals = [];
        $divisiPerempuanTotals = [];

        foreach ($listDivisi as $divisi) {
            $countLaki = 0;
            $countPerempuan = 0;

            foreach ($divisi->listKelas as $kelas) {
                $countLaki += $kelas->listMurid->where('jenis_kelamin', '1')->where('status', true)->count();
                $countPerempuan += $kelas->listMurid->where('jenis_kelamin', '0')->where('status', true)->count();
            }

            $countTotal = $countLaki + $countPerempuan;
            $totalMurid += $countTotal;
            $totalMuridLaki += $countLaki;
            $totalMuridPerempuan += $countPerempuan;

            $divisiLabels[] = $divisi->nama;
            $divisiTotals[] = $countTotal;
            $divisiLakiTotals[] = $countLaki;
            $divisiPerempuanTotals[] = $countPerempuan;
        }

        // 3. Statistik Data Jamaah Kelompok
        $totalJamaah = KelompokJamaah::where('status', true)->count();
        $jamaahLaki = KelompokJamaah::where('status', true)->where('jenis_kelamin', 1)->count();
        $jamaahPerempuan = KelompokJamaah::where('status', true)->where('jenis_kelamin', 0)->count();
        $jamaahUmumCount = KelompokJamaah::where('status', true)->whereNull('divisi_id')->count();
        $jamaahGenerusCount = KelompokJamaah::where('status', true)->whereNotNull('divisi_id')->count();

        // 4. Jadwal Kegiatan & Monitoring Presensi Terkini
        $jadwalAktifBulanIni = KelompokJadwal::where('status', true)
            ->whereMonth('tanggal', Carbon::now()->month)
            ->whereYear('tanggal', Carbon::now()->year)
            ->count();

        $latestJadwal = KelompokJadwal::with(['getKegiatan', 'getLokasi'])
            ->where('status', true)
            ->orderBy('tanggal', 'DESC')
            ->orderBy('jam_mulai', 'DESC')
            ->first();

        $latestPresensiStats = [
            'hadir'  => 0,
            'sdc'    => 0,
            'izin'   => 0,
            'total'  => 0,
            'persen' => 0,
        ];

        if ($latestJadwal) {
            $countHadir = KelompokAbsensi::where('jadwal_id', $latestJadwal->id)->where('status', 'hadir')->count();
            $countSDC   = KelompokAbsensi::where('jadwal_id', $latestJadwal->id)->where('status', 'sdc')->count();
            $countIzin  = KelompokAbsensi::where('jadwal_id', $latestJadwal->id)->where('status', 'izin')->count();
            $totalRecorded = $countHadir + $countSDC + $countIzin;

            $persen = $totalJamaah > 0 ? round(($countHadir / $totalJamaah) * 100) : 0;

            $latestPresensiStats = [
                'hadir'  => $countHadir,
                'sdc'    => $countSDC,
                'izin'   => $countIzin,
                'total'  => $totalRecorded,
                'persen' => $persen,
            ];
        }

        // Jadwal Mendatang (Upcoming)
        $upcomingJadwals = KelompokJadwal::with(['getKegiatan', 'getLokasi'])
            ->where('status', true)
            ->whereDate('tanggal', '>=', Carbon::today())
            ->orderBy('tanggal', 'ASC')
            ->orderBy('jam_mulai', 'ASC')
            ->take(3)
            ->get();

        return view('pages.dashboard.index', compact(
            'listDivisi',
            'totalMurid',
            'totalMuridLaki',
            'totalMuridPerempuan',
            'divisiLabels',
            'divisiTotals',
            'divisiLakiTotals',
            'divisiPerempuanTotals',
            'totalJamaah',
            'jamaahLaki',
            'jamaahPerempuan',
            'jamaahUmumCount',
            'jamaahGenerusCount',
            'jadwalAktifBulanIni',
            'latestJadwal',
            'latestPresensiStats',
            'upcomingJadwals',
            'user'
        ));
    }
}
