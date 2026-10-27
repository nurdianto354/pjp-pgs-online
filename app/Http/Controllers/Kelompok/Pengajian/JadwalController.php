<?php

namespace App\Http\Controllers\Kelompok\Pengajian;

use App\Http\Controllers\Controller;
use App\Models\Kelompok\MasterData\Pengajian;
use Illuminate\Http\Request;

class JadwalController extends Controller
{
    public function __construct()
    {
        $this->middleware(['permission:master_data']);
    }

    public function index()
    {
        $datas = Pengajian::orderBy('created_at', 'DESC')->get();

        return view('pages.kelompok.master_data.pengajian.index', compact('datas'));
    }
}
