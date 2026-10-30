@extends('layouts.app')

@section('content')
<div class="content-header pb-1">
    <div class="container-fluid">
        <!-- ZONA 1: WELCOME BANNER & QUICK ACTIONS -->
        <div class="card border-0 shadow-sm mb-3 overflow-hidden" style="border-radius: 16px; background: linear-gradient(135deg, #0d4a2d 0%, #1e7e34 100%);">
            <div class="card-body p-3 p-md-4 text-white position-relative">
                <div class="row align-items-center">
                    <div class="col-lg-7 col-md-12 mb-3 mb-lg-0">
                        <div class="d-inline-flex align-items-center mb-2 px-2 py-1 rounded-pill" style="background: rgba(255, 255, 255, 0.2); font-size: 0.78rem;">
                            <i class="fas fa-calendar-day mr-1"></i>
                            <span>{{ \Carbon\Carbon::now()->isoFormat('dddd, D MMMM Y') }}</span>
                            <span class="mx-2">&bull;</span>
                            <i class="fas fa-map-marker-alt mr-1"></i>
                            <span>Kelompok Pagesangan II</span>
                        </div>
                        <h2 class="font-weight-bold mb-1" style="letter-spacing: -0.5px;">
                            Selamat Datang, {{ $user->nama ?? $user->username }} 👋
                        </h2>
                        <p class="mb-0 opacity-90 small" style="line-height: 1.5; color: rgba(255,255,255,0.9);">
                            Sistem Terpadu Pembinaan Generus, Pengelolaan Data Jamaah, & Presensi Geofencing GPS.
                        </p>
                    </div>
                    <div class="col-lg-5 col-md-12 text-lg-right">
                        <div class="d-flex flex-wrap justify-content-lg-end gap-2">
                            @if($latestJadwal)
                                <a href="{{ route('kelompok.absensi.public_form', $latestJadwal->id) }}" target="_blank" class="btn btn-warning btn-sm font-weight-bold shadow-sm mb-1 mr-1 text-dark" style="border-radius: 10px;">
                                    <i class="fas fa-qrcode mr-1"></i> Form Presensi Terkini
                                </a>
                            @endif
                            <a href="{{ route('kelompok.jadwal.index') }}" class="btn btn-light btn-sm font-weight-bold shadow-sm mb-1 mr-1 text-success" style="border-radius: 10px;">
                                <i class="fas fa-calendar-plus mr-1"></i> Jadwal Kegiatan
                            </a>
                            <a href="{{ route('kelompok.master_data.jamaah.index') }}" class="btn btn-outline-light btn-sm font-weight-bold shadow-sm mb-1" style="border-radius: 10px;">
                                <i class="fas fa-users mr-1"></i> Data Jamaah
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ZONA 2: 4 KPI SUMMARY CARDS (STATISTIK UTAMA) -->
        <div class="row">
            <!-- 1. Total Siswa Generus -->
            <div class="col-xl-3 col-md-6 col-12 mb-3">
                <div class="card border-0 shadow-sm h-100 kpi-card" style="border-radius: 14px; border-left: 5px solid #28a745 !important;">
                    <div class="card-body p-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <span class="text-uppercase text-muted font-weight-bold" style="font-size: 0.72rem; letter-spacing: 0.5px;">
                                    Total Siswa Generus
                                </span>
                                <h3 class="font-weight-bold text-dark my-1">{{ number_format($totalMurid, 0, ',', '.') }}</h3>
                                <div class="small text-muted">
                                    <span class="text-info font-weight-bold mr-2"><i class="fas fa-mars mr-1"></i>{{ $totalMuridLaki }} Laki</span>
                                    <span class="text-danger font-weight-bold"><i class="fas fa-venus mr-1"></i>{{ $totalMuridPerempuan }} Pr</span>
                                </div>
                            </div>
                            <div class="kpi-icon-box bg-success-light text-success">
                                <i class="fas fa-user-graduate fa-lg"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 2. Total Data Jamaah Kelompok -->
            <div class="col-xl-3 col-md-6 col-12 mb-3">
                <div class="card border-0 shadow-sm h-100 kpi-card" style="border-radius: 14px; border-left: 5px solid #17a2b8 !important;">
                    <div class="card-body p-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <span class="text-uppercase text-muted font-weight-bold" style="font-size: 0.72rem; letter-spacing: 0.5px;">
                                    Data Jamaah Kelompok
                                </span>
                                <h3 class="font-weight-bold text-dark my-1">{{ number_format($totalJamaah, 0, ',', '.') }}</h3>
                                <div class="small text-muted">
                                    <span class="text-secondary font-weight-bold mr-2"><i class="fas fa-users mr-1"></i>{{ $jamaahUmumCount }} Umum</span>
                                    <span class="text-primary font-weight-bold"><i class="fas fa-layer-group mr-1"></i>{{ $jamaahGenerusCount }} Murid</span>
                                </div>
                            </div>
                            <div class="kpi-icon-box bg-info-light text-info">
                                <i class="fas fa-mosque fa-lg"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 3. Jadwal Kegiatan Aktif Bulan Ini -->
            <div class="col-xl-3 col-md-6 col-12 mb-3">
                <div class="card border-0 shadow-sm h-100 kpi-card" style="border-radius: 14px; border-left: 5px solid #fd7e14 !important;">
                    <div class="card-body p-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <span class="text-uppercase text-muted font-weight-bold" style="font-size: 0.72rem; letter-spacing: 0.5px;">
                                    Kegiatan Bulan Ini
                                </span>
                                <h3 class="font-weight-bold text-dark my-1">{{ $jadwalAktifBulanIni }} Sesi</h3>
                                <div class="small text-muted">
                                    <span class="text-success font-weight-bold">
                                        <i class="fas fa-check-circle mr-1"></i>Bulan {{ \Carbon\Carbon::now()->isoFormat('MMMM Y') }}
                                    </span>
                                </div>
                            </div>
                            <div class="kpi-icon-box bg-warning-light text-warning">
                                <i class="fas fa-calendar-check fa-lg"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 4. Kehadiran Presensi Terkini -->
            <div class="col-xl-3 col-md-6 col-12 mb-3">
                <div class="card border-0 shadow-sm h-100 kpi-card" style="border-radius: 14px; border-left: 5px solid #6f42c1 !important;">
                    <div class="card-body p-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <span class="text-uppercase text-muted font-weight-bold" style="font-size: 0.72rem; letter-spacing: 0.5px;">
                                    Presensi Kegiatan Terkini
                                </span>
                                <h3 class="font-weight-bold text-dark my-1">{{ $latestPresensiStats['total'] }} Jamaah</h3>
                                <div class="small text-muted">
                                    <span class="badge badge-success px-1">{{ $latestPresensiStats['hadir'] }} Hadir</span>
                                    <span class="badge badge-primary px-1">{{ $latestPresensiStats['sdc'] }} SDC</span>
                                    <span class="badge badge-warning text-dark px-1">{{ $latestPresensiStats['izin'] }} Izin</span>
                                </div>
                            </div>
                            <div class="kpi-icon-box bg-purple-light text-purple">
                                <i class="fas fa-map-marked-alt fa-lg"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">
        <!-- ZONA 3: CHARTS & MONITORING WIDGET -->
        <div class="row">
            <!-- Grafik Distribusi Generus per Divisi -->
            <div class="col-lg-7 col-12 mb-3">
                <div class="card border-0 shadow-sm h-100" style="border-radius: 16px;">
                    <div class="card-header bg-white border-0 pt-3 pb-2 d-flex justify-content-between align-items-center">
                        <h6 class="font-weight-bold text-dark m-0">
                            <i class="fas fa-chart-bar text-success mr-2"></i>Komposisi Siswa Generus per Divisi
                        </h6>
                        <span class="badge badge-light border text-muted small px-2 py-1">Laki-laki & Perempuan</span>
                    </div>
                    <div class="card-body pt-1 pb-3">
                        <div style="position: relative; height: 260px;">
                            <canvas id="chartDivisiGenerus"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Widget Kegiatan & Monitoring Presensi Terkini -->
            <div class="col-lg-5 col-12 mb-3">
                <div class="card border-0 shadow-sm h-100" style="border-radius: 16px;">
                    <div class="card-header bg-white border-0 pt-3 pb-2 d-flex justify-content-between align-items-center">
                        <h6 class="font-weight-bold text-dark m-0">
                            <i class="fas fa-satellite-dish text-primary mr-2"></i>Kegiatan & Presensi Live
                        </h6>
                        @if($latestJadwal)
                            <span class="badge badge-success px-2 py-1">Aktif</span>
                        @endif
                    </div>
                    <div class="card-body pt-1 pb-3">
                        @if($latestJadwal)
                            <div class="p-3 rounded mb-3" style="background: #f8fafc; border: 1px solid #e2e8f0;">
                                <h6 class="font-weight-bold text-dark mb-1">
                                    {{ $latestJadwal->getKegiatan ? $latestJadwal->getKegiatan->nama : 'Jadwal Kegiatan' }}
                                </h6>
                                <div class="text-muted small mb-2">
                                    <span class="mr-2"><i class="far fa-calendar-alt text-primary mr-1"></i>{{ $latestJadwal->tanggal ? $latestJadwal->tanggal->format('d M Y') : '-' }}</span>
                                    <span><i class="far fa-clock text-info mr-1"></i>{{ substr($latestJadwal->jam_mulai, 0, 5) }} - {{ substr($latestJadwal->jam_selesai, 0, 5) }} WIB</span>
                                </div>
                                
                                <!-- Lokasi Pelaksanaan -->
                                <div class="small text-muted mb-2">
                                    <i class="fas fa-map-pin text-danger mr-1"></i>
                                    @forelse($latestJadwal->getLokasi as $loc)
                                        <span class="badge badge-light border mr-1">{{ $loc->nama }} ({{ $loc->radius ?? 100 }}m)</span>
                                    @empty
                                        <span class="font-italic">- Titik Pusat -</span>
                                    @endforelse
                                </div>

                                <!-- Progress Presensi -->
                                <div class="mb-2">
                                    <div class="d-flex justify-content-between small font-weight-bold mb-1">
                                        <span>Total Presensi Masuk:</span>
                                        <span class="text-success">{{ $latestPresensiStats['total'] }} Orang</span>
                                    </div>
                                    <div class="progress" style="height: 10px; border-radius: 5px;">
                                        @php
                                            $totalPresensi = max(1, $latestPresensiStats['total']);
                                            $persenHadir = round(($latestPresensiStats['hadir'] / $totalPresensi) * 100);
                                            $persenSDC = round(($latestPresensiStats['sdc'] / $totalPresensi) * 100);
                                            $persenIzin = round(($latestPresensiStats['izin'] / $totalPresensi) * 100);
                                        @endphp
                                        <div class="progress-bar bg-success" style="width: {{ $persenHadir }}%" title="Hadir: {{ $latestPresensiStats['hadir'] }}"></div>
                                        <div class="progress-bar bg-primary" style="width: {{ $persenSDC }}%" title="SDC: {{ $latestPresensiStats['sdc'] }}"></div>
                                        <div class="progress-bar bg-warning" style="width: {{ $persenIzin }}%" title="Izin: {{ $latestPresensiStats['izin'] }}"></div>
                                    </div>
                                </div>

                                <!-- Tombol Aksi Langsung -->
                                <div class="d-flex flex-wrap gap-1 mt-3">
                                    <a href="{{ route('kelompok.absensi.public_rekap', $latestJadwal->id) }}" target="_blank" class="btn btn-outline-info btn-sm flex-fill font-weight-bold mr-1 mb-1" style="border-radius: 8px;">
                                        <i class="fas fa-list-alt mr-1"></i> Rekap Publik
                                    </a>
                                    <a href="{{ route('kelompok.absensi.detail', $latestJadwal->id) }}" class="btn btn-outline-secondary btn-sm flex-fill font-weight-bold mb-1" style="border-radius: 8px;">
                                        <i class="fas fa-chart-pie mr-1"></i> Detail Admin
                                    </a>
                                </div>
                            </div>
                        @else
                            <div class="text-center py-4 text-muted">
                                <i class="fas fa-calendar-times fa-3x mb-2 text-secondary"></i>
                                <p class="small mb-2">Belum ada kegiatan yang aktif saat ini.</p>
                                <a href="{{ route('kelompok.jadwal.index') }}" class="btn btn-success btn-sm font-weight-bold">
                                    <i class="fas fa-plus mr-1"></i> Buat Jadwal Baru
                                </a>
                            </div>
                        @endif

                        <!-- Jadwal Mendatang Lainnya -->
                        @if($upcomingJadwals->count() > 1)
                            <div class="mt-2 pt-2 border-top">
                                <span class="font-weight-bold small text-muted text-uppercase d-block mb-1">
                                    <i class="far fa-calendar mr-1"></i> Jadwal Berikutnya:
                                </span>
                                @foreach($upcomingJadwals->skip(1) as $next)
                                    <div class="d-flex justify-content-between align-items-center py-1 small border-bottom">
                                        <span class="text-dark font-weight-bold text-truncate" style="max-width: 65%;">
                                            {{ $next->getKegiatan ? $next->getKegiatan->nama : 'Jadwal' }}
                                        </span>
                                        <span class="text-muted">{{ $next->tanggal ? $next->tanggal->format('d M') : '-' }} &bull; {{ substr($next->jam_mulai, 0, 5) }}</span>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- ZONA 4: TABEL SENSUS SISWA PER KELAS GENERUS (MODERN TABS) -->
        <div class="card border-0 shadow-sm mb-4" style="border-radius: 16px;">
            <div class="card-header bg-white border-0 pt-3 pb-2">
                <div class="d-flex justify-content-between align-items-center flex-wrap">
                    <div>
                        <h6 class="font-weight-bold text-dark m-0">
                            <i class="fas fa-clipboard-list text-success mr-2"></i>Sensus Siswa & Kelas Pembinaan Generus
                        </h6>
                        <small class="text-muted">Rincian data murid aktif per divisi dan per kelas</small>
                    </div>
                    <!-- Nav Tabs Modern -->
                    <ul class="nav nav-pills mt-2 mt-md-0" id="divisiPills" role="tablist">
                        @foreach ($listDivisi as $index => $divisi)
                            <li class="nav-item">
                                <a class="nav-link {{ $index === 0 ? 'active' : '' }} font-weight-bold py-1 px-3 small" 
                                   id="pills-tab-{{ $divisi->id }}" 
                                   data-toggle="pill" 
                                   href="#pills-{{ $divisi->id }}" 
                                   role="tab" 
                                   style="border-radius: 20px;">
                                    {{ $divisi->nama }}
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>

            <div class="card-body p-0">
                <div class="tab-content" id="divisiPillsContent">
                    @foreach ($listDivisi as $index => $divisi)
                        <div class="tab-pane fade {{ $index === 0 ? 'show active' : '' }}" id="pills-{{ $divisi->id }}" role="tabpanel">
                            @php
                                $totalMuridDivisi       = 0;
                                $totalMuridMaleDivisi   = 0;
                                $totalMuridFemaleDivisi = 0;
                                $num = 0;
                            @endphp
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0">
                                    <thead class="bg-light text-muted small text-uppercase">
                                        <tr>
                                            <th style="width: 5%;" class="text-center">No</th>
                                            <th style="width: 40%;">Nama Kelas</th>
                                            <th style="width: 18%;" class="text-center"><i class="fas fa-mars text-info mr-1"></i>Laki-laki</th>
                                            <th style="width: 18%;" class="text-center"><i class="fas fa-venus text-danger mr-1"></i>Perempuan</th>
                                            <th style="width: 19%;" class="text-center"><i class="fas fa-users text-success mr-1"></i>Total Siswa</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($divisi->listKelas as $kelas)
                                            @php
                                                $countLaki = $kelas->listMurid->where('jenis_kelamin', '1')->where('status', true)->count();
                                                $countPerempuan = $kelas->listMurid->where('jenis_kelamin', '0')->where('status', true)->count();
                                                $countTotal = $kelas->listMurid->where('status', true)->count();

                                                $totalMuridMaleDivisi += $countLaki;
                                                $totalMuridFemaleDivisi += $countPerempuan;
                                                $totalMuridDivisi += $countTotal;
                                            @endphp
                                            @if($countTotal > 0)
                                                <tr>
                                                    <td class="text-center font-weight-bold text-muted">{{ ++$num }}</td>
                                                    <td>
                                                        <span class="font-weight-bold text-dark">{{ $kelas->nama }}</span>
                                                    </td>
                                                    <td class="text-center font-weight-bold text-info">{{ $countLaki }}</td>
                                                    <td class="text-center font-weight-bold text-danger">{{ $countPerempuan }}</td>
                                                    <td class="text-center">
                                                        <span class="badge badge-success px-2 py-1 font-weight-bold">{{ $countTotal }} Siswa</span>
                                                    </td>
                                                </tr>
                                            @endif
                                        @empty
                                            <tr>
                                                <td colspan="5" class="text-center text-muted py-4">Belum ada kelas terdaftar pada divisi ini.</td>
                                            </tr>
                                        @endforelse

                                        @if($num === 0 && count($divisi->listKelas) > 0)
                                            <tr>
                                                <td colspan="5" class="text-center text-muted py-4">Belum ada murid aktif yang terdaftar di kelas divisi ini.</td>
                                            </tr>
                                        @endif
                                    </tbody>
                                    <tfoot class="bg-light font-weight-bold">
                                        <tr>
                                            <td colspan="2" class="text-right">Total Siswa {{ $divisi->nama }}:</td>
                                            <td class="text-center text-info">{{ $totalMuridMaleDivisi }}</td>
                                            <td class="text-center text-danger">{{ $totalMuridFemaleDivisi }}</td>
                                            <td class="text-center text-success h6 font-weight-bold mb-0">{{ $totalMuridDivisi }} Siswa</td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</section>
@endsection

@section('js')
<!-- Chart.js Plugin -->
<script src="{{ asset('assets/plugins/chart.js/Chart.bundle.min.js') }}"></script>
<script>
    $(document).ready(function () {
        // Data dari Controller untuk Chart
        const labels = @json($divisiLabels);
        const dataLaki = @json($divisiLakiTotals);
        const dataPerempuan = @json($divisiPerempuanTotals);

        const ctx = document.getElementById('chartDivisiGenerus');
        if (ctx) {
            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [
                        {
                            label: 'Laki-laki',
                            backgroundColor: '#17a2b8',
                            borderColor: '#117a8b',
                            borderWidth: 1,
                            borderRadius: 6,
                            data: dataLaki
                        },
                        {
                            label: 'Perempuan',
                            backgroundColor: '#e83e8c',
                            borderColor: '#d63384',
                            borderWidth: 1,
                            borderRadius: 6,
                            data: dataPerempuan
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    legend: {
                        position: 'top',
                        labels: {
                            boxWidth: 14,
                            fontFamily: 'Poppins',
                            fontSize: 12
                        }
                    },
                    scales: {
                        xAxes: [{
                            gridLines: { display: false },
                            ticks: {
                                fontFamily: 'Poppins',
                                fontSize: 12
                            }
                        }],
                        yAxes: [{
                            gridLines: {
                                color: '#f1f5f9',
                                zeroLineColor: '#e2e8f0'
                            },
                            ticks: {
                                beginAtZero: true,
                                stepSize: 5,
                                fontFamily: 'Poppins',
                                fontSize: 11
                            }
                        }]
                    },
                    tooltips: {
                        mode: 'index',
                        intersect: false,
                        backgroundColor: 'rgba(15, 23, 42, 0.85)',
                        titleFontFamily: 'Poppins',
                        bodyFontFamily: 'Poppins',
                        cornerRadius: 8,
                        xPadding: 10,
                        yPadding: 10
                    }
                }
            });
        }
    });
</script>

<style>
    .kpi-card {
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }
    .kpi-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 10px 20px rgba(0, 0, 0, 0.08) !important;
    }
    .kpi-icon-box {
        width: 48px;
        height: 48px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .bg-success-light { background-color: #eafaf1; }
    .bg-info-light { background-color: #ebf5fb; }
    .bg-warning-light { background-color: #fef9e7; }
    .bg-purple-light { background-color: #f5eeff; }
    .text-purple { color: #6f42c1; }

    .nav-pills .nav-link.active {
        background-color: #28a745 !important;
        color: #ffffff !important;
        box-shadow: 0 4px 10px rgba(40, 167, 69, 0.3);
    }
    .nav-pills .nav-link {
        color: #4b5563;
        margin-left: 4px;
        transition: all 0.2s;
    }
    .nav-pills .nav-link:hover {
        background-color: #f3f4f6;
    }
</style>
@endsection
