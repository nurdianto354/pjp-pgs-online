<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Rekap Kehadiran - {{ $jadwal->getKegiatan ? $jadwal->getKegiatan->nama : 'Presensi Kegiatan' }}</title>
    
    <!-- Google Fonts & FontAwesome -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <!-- Bootstrap 4.6 CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">

    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #eef2f7 0%, #dbe4ee 100%);
            min-height: 100vh;
            color: #333;
        }
        .main-card {
            border: none;
            border-radius: 16px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
            background: #ffffff;
            overflow: hidden;
        }
        .header-box {
            background: linear-gradient(135deg, #1e7e34 0%, #28a745 100%);
            color: #fff;
            padding: 24px 20px;
            text-align: center;
        }
        .filter-btn-group .btn {
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
            padding: 6px 16px;
            margin: 2px 4px;
            transition: all 0.2s ease-in-out;
        }
        .filter-btn-group .btn.active {
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.15);
            transform: translateY(-1px);
        }
        .stat-card {
            border-radius: 12px;
            padding: 14px 16px;
            margin-bottom: 12px;
            border: 1px solid rgba(0,0,0,0.06);
            transition: transform 0.2s;
        }
        .stat-card:hover {
            transform: translateY(-2px);
        }
        .stat-card.stat-hadir {
            background: #eafaf1;
            border-left: 5px solid #28a745;
        }
        .stat-card.stat-sdc {
            background: #ebf5fb;
            border-left: 5px solid #007bff;
        }
        .stat-card.stat-izin {
            background: #fef9e7;
            border-left: 5px solid #ffc107;
        }
        .stat-card.stat-total {
            background: #f4f6f9;
            border-left: 5px solid #6c757d;
        }
        .stat-number {
            font-size: 1.6rem;
            font-weight: 700;
            line-height: 1.2;
        }
        .badge-divisi {
            font-size: 0.75rem;
            font-weight: 500;
            padding: 4px 8px;
            border-radius: 6px;
        }
        .attendee-item {
            background: #ffffff;
            border: 1px solid #e9ecef;
            border-radius: 12px;
            padding: 14px 16px;
            margin-bottom: 10px;
            transition: all 0.2s;
        }
        .attendee-item:hover {
            border-color: #28a745;
            box-shadow: 0 4px 12px rgba(0,0,0,0.05);
        }
        .status-badge {
            font-size: 0.8rem;
            font-weight: 600;
            padding: 5px 12px;
            border-radius: 20px;
        }
        .search-box {
            border-radius: 25px;
            padding-left: 40px;
            font-size: 0.9rem;
            border: 1px solid #ced4da;
        }
        .search-icon-wrapper {
            position: absolute;
            left: 16px;
            top: 10px;
            color: #6c757d;
        }
        .btn-action-top {
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
        }
    </style>
</head>
<body class="py-3 py-md-4">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-12 col-lg-10">
                <div class="main-card">
                    
                    <!-- Header -->
                    <div class="header-box">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <a href="{{ route('kelompok.absensi.public_form', $jadwal->id) }}" class="btn btn-sm btn-light btn-action-top shadow-sm text-success">
                                <i class="fas fa-arrow-left mr-1"></i> Form Presensi
                            </a>
                            <button type="button" class="btn btn-sm btn-outline-light btn-action-top" onclick="window.location.reload();">
                                <i class="fas fa-sync-alt mr-1"></i> Refresh
                            </button>
                        </div>
                        <h4 class="font-weight-bold mb-1">
                            <i class="fas fa-clipboard-check mr-2"></i> Rekap Kehadiran Kegiatan
                        </h4>
                        <h6 class="font-weight-bold mb-2 text-white-50">
                            {{ $jadwal->getKegiatan ? $jadwal->getKegiatan->nama : 'Kegiatan Kelompok' }}
                        </h6>
                        <div class="small text-white-50">
                            <span class="mr-3">
                                <i class="far fa-calendar-alt mr-1"></i>
                                {{ $jadwal->tanggal ? $jadwal->tanggal->format('d M Y') : '-' }}
                            </span>
                            <span>
                                <i class="far fa-clock mr-1"></i>
                                {{ substr($jadwal->jam_mulai, 0, 5) }} - {{ substr($jadwal->jam_selesai, 0, 5) }} WIB
                            </span>
                        </div>
                    </div>

                    <div class="card-body p-3 p-md-4">
                        
                        <!-- Filter Jenis Kelamin -->
                        <div class="mb-3 text-center">
                            <label class="font-weight-bold text-muted small d-block mb-2">
                                <i class="fas fa-filter text-primary mr-1"></i> Filter Berdasarkan Jenis Kelamin:
                            </label>
                            <div class="filter-btn-group d-flex justify-content-center flex-wrap">
                                <a href="javascript:void(0)" class="btn btn-filter {{ $filterJk === null || $filterJk === '' ? 'btn-primary active' : 'btn-outline-primary' }}" data-jk="all">
                                    <i class="fas fa-users mr-1"></i> Semua (<span id="countPillAll">{{ $totalPresensi }}</span>)
                                </a>
                                <a href="javascript:void(0)" class="btn btn-filter {{ $filterJk === '1' ? 'btn-info active' : 'btn-outline-info' }}" data-jk="1">
                                    <i class="fas fa-mars mr-1"></i> Laki-laki (<span id="countPillLaki">{{ $totalLakiSudah }}</span>)
                                </a>
                                <a href="javascript:void(0)" class="btn btn-filter {{ $filterJk === '0' ? 'btn-danger active' : 'btn-outline-danger' }}" data-jk="0">
                                    <i class="fas fa-venus mr-1"></i> Perempuan (<span id="countPillPerempuan">{{ $totalPerempuanSudah }}</span>)
                                </a>
                            </div>
                        </div>

                        <!-- Kartu Ringkasan Statistik -->
                        <div class="row no-gutters row-cols-2 row-cols-md-4 text-center">
                            <div class="col px-1">
                                <div class="stat-card stat-hadir">
                                    <div class="text-success small font-weight-bold text-uppercase">Hadir</div>
                                    <div class="stat-number text-success" id="statHadir">{{ $countHadir }}</div>
                                    <small class="text-muted d-none d-sm-block">In-Radius GPS</small>
                                </div>
                            </div>
                            <div class="col px-1">
                                <div class="stat-card stat-sdc">
                                    <div class="text-primary small font-weight-bold text-uppercase">SDC</div>
                                    <div class="stat-number text-primary" id="statSDC">{{ $countSDC }}</div>
                                    <small class="text-muted d-none d-sm-block">Sambung Dinas</small>
                                </div>
                            </div>
                            <div class="col px-1">
                                <div class="stat-card stat-izin">
                                    <div class="text-warning small font-weight-bold text-uppercase">Izin</div>
                                    <div class="stat-number text-warning" id="statIzin">{{ $countIzin }}</div>
                                    <small class="text-muted d-none d-sm-block">Ada Alasan</small>
                                </div>
                            </div>
                            <div class="col px-1">
                                <div class="stat-card stat-total">
                                    <div class="text-secondary small font-weight-bold text-uppercase">Total</div>
                                    <div class="stat-number text-dark" id="statTotal">{{ $totalPresensi }}</div>
                                    <small class="text-muted d-none d-sm-block">Sudah Presensi</small>
                                </div>
                            </div>
                        </div>

                        <!-- Input Pencarian Cepat -->
                        <div class="position-relative my-3">
                            <i class="fas fa-search search-icon-wrapper"></i>
                            <input type="text" id="searchInput" class="form-control search-box" placeholder="Ketik nama jamaah atau divisi untuk mencari...">
                        </div>

                        <!-- List Peserta Hadir / Presensi -->
                        <div class="d-flex justify-content-between align-items-center mb-2 px-1">
                            <div class="font-weight-bold text-dark small" id="listHeaderInfo">
                                <i class="fas fa-list-ol text-success mr-1"></i> Daftar Jamaah yang Telah Presensi (<span id="visibleCount">{{ $presensis->count() }}</span> orang):
                            </div>
                            <div class="small text-muted font-italic d-none d-sm-block">
                                Diurutkan dari yang terbaru
                            </div>
                        </div>

                        <div id="attendeesContainer">
                            @forelse($presensis as $key => $p)
                                @php
                                    $jamaah = $p->getJamaah;
                                    $namaJamaah = $jamaah ? $jamaah->nama_lengkap : 'Jamaah ID: ' . $p->jamaah_id;
                                    $jk = $jamaah ? (int)$jamaah->jenis_kelamin : 1;
                                    $divisiNama = $jamaah && $jamaah->getDivisi ? $jamaah->getDivisi->nama : 'Kelompok';
                                    $waktu = $p->waktu_presensi ? \Carbon\Carbon::parse($p->waktu_presensi)->format('H:i') : '-';
                                @endphp
                                <div class="attendee-item" 
                                     data-jk="{{ $jk }}" 
                                     data-status="{{ $p->status }}"
                                     data-name="{{ strtolower($namaJamaah) }}" 
                                     data-divisi="{{ strtolower($divisiNama) }}">
                                    <div class="d-flex justify-content-between align-items-start flex-wrap">
                                        <div class="pr-2 mb-1">
                                            <div class="font-weight-bold text-dark mb-1" style="font-size: 0.95rem;">
                                                <span class="text-muted mr-1">#{{ $loop->iteration }}</span>
                                                {{ $namaJamaah }}
                                            </div>
                                            <div class="d-flex flex-wrap align-items-center gap-1 small">
                                                <!-- Badge Gender -->
                                                @if($jk == 1)
                                                    <span class="badge badge-info px-2 py-1 mr-1">
                                                        <i class="fas fa-mars mr-1"></i> Laki-laki
                                                    </span>
                                                @else
                                                    <span class="badge badge-danger px-2 py-1 mr-1">
                                                        <i class="fas fa-venus mr-1"></i> Perempuan
                                                    </span>
                                                @endif

                                                <!-- Badge Divisi -->
                                                @if($jamaah && !empty($jamaah->getDivisi))
                                                    <span class="badge badge-secondary px-2 py-1 mr-1">
                                                        <i class="fas fa-layer-group mr-1"></i> {{ $divisiNama }}
                                                    </span>
                                                @else
                                                    <span class="badge badge-success px-2 py-1 mr-1" title="Divisi Kelompok">
                                                        <i class="fas fa-users mr-1"></i> Kelompok
                                                    </span>
                                                @endif

                                                <span class="text-muted ml-1">
                                                    <i class="far fa-clock mr-1"></i> Pukul {{ $waktu }} WIB
                                                </span>
                                            </div>
                                        </div>

                                        <div class="text-right ml-auto">
                                            @if($p->status == 'hadir')
                                                <span class="status-badge badge-success text-white shadow-sm">
                                                    <i class="fas fa-check-circle mr-1"></i> Hadir
                                                </span>
                                                @if($p->lokasi_id && $p->getLokasi)
                                                    <div class="small text-muted mt-1" style="font-size: 0.75rem;">
                                                        <i class="fas fa-map-marker-alt text-danger mr-1"></i> {{ $p->getLokasi->nama }}
                                                        @if($p->jarak_meter !== null)
                                                            ({{ $p->jarak_meter }}m)
                                                        @endif
                                                    </div>
                                                @endif
                                            @elseif($p->status == 'sdc')
                                                <span class="status-badge badge-primary text-white shadow-sm">
                                                    <i class="fas fa-briefcase mr-1"></i> SDC
                                                </span>
                                            @elseif($p->status == 'izin')
                                                <span class="status-badge badge-warning text-dark shadow-sm">
                                                    <i class="fas fa-envelope-open-text mr-1"></i> Izin
                                                </span>
                                            @endif
                                        </div>
                                    </div>

                                    @if(in_array($p->status, ['sdc', 'izin']) && !empty($p->keterangan))
                                        <div class="mt-2 pt-2 border-top small text-muted bg-light p-2 rounded">
                                            <i class="fas fa-comment-alt text-secondary mr-1"></i> <strong>Keterangan:</strong> {{ $p->keterangan }}
                                        </div>
                                    @endif
                                </div>
                            @empty
                                <div id="emptyNotice" class="text-center py-5">
                                    <i class="fas fa-clipboard-list text-muted fa-3x mb-3"></i>
                                    <h6 class="font-weight-bold text-dark">Belum Ada Presensi Masuk</h6>
                                    <p class="text-muted small mb-3">Jadilah yang pertama melakukan presensi kehadiran pada kegiatan ini.</p>
                                    <a href="{{ route('kelompok.absensi.public_form', $jadwal->id) }}" class="btn btn-success btn-sm shadow-sm">
                                        <i class="fas fa-edit mr-1"></i> Isi Presensi Sekarang
                                    </a>
                                </div>
                            @endforelse

                            <div id="noMatchNotice" class="text-center py-4" style="display: none;">
                                <i class="fas fa-search text-muted fa-2x mb-2"></i>
                                <h6 class="font-weight-bold text-dark">Data Tidak Ditemukan</h6>
                                <p class="text-muted small">Tidak ada jamaah yang cocok dengan filter atau kata kunci pencarian Anda.</p>
                            </div>
                        </div>

                        <!-- Footer navigasi -->
                        <div class="mt-4 pt-3 border-top text-center">
                            <a href="{{ route('kelompok.absensi.public_form', $jadwal->id) }}" class="btn btn-success btn-block py-2 font-weight-bold shadow-sm">
                                <i class="fas fa-edit mr-1"></i> Buka Form untuk Isi Presensi Jamaah
                            </a>
                        </div>
                    </div>

                    <div class="card-footer bg-light text-center py-2 text-muted small">
                        PJP Online &copy; {{ date('Y') }} - Rekap Presensi Kegiatan Kelompok
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Script jQuery & Bootstrap -->
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.5.1/dist/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        let currentFilterJk = "{{ $filterJk !== null && $filterJk !== '' ? $filterJk : 'all' }}";

        $(document).ready(function () {
            applyFilters();

            // Klik Filter Jenis Kelamin
            $('.btn-filter').on('click', function () {
                const jk = $(this).data('jk');
                currentFilterJk = jk.toString();

                $('.btn-filter').removeClass('active btn-primary btn-info btn-danger')
                               .addClass(function () {
                                   const dataJk = $(this).data('jk').toString();
                                   if (dataJk === 'all') return 'btn-outline-primary';
                                   if (dataJk === '1') return 'btn-outline-info';
                                   if (dataJk === '0') return 'btn-outline-danger';
                               });

                if (currentFilterJk === 'all') {
                    $(this).removeClass('btn-outline-primary').addClass('btn-primary active');
                } else if (currentFilterJk === '1') {
                    $(this).removeClass('btn-outline-info').addClass('btn-info active');
                } else if (currentFilterJk === '0') {
                    $(this).removeClass('btn-outline-danger').addClass('btn-danger active');
                }

                applyFilters();
            });

            // Live Search Input
            $('#searchInput').on('keyup', function () {
                applyFilters();
            });
        });

        function applyFilters() {
            const query = $('#searchInput').val().toLowerCase().trim();
            let countVisible = 0;
            let countHadir = 0;
            let countSDC = 0;
            let countIzin = 0;

            const items = $('.attendee-item');

            if (items.length === 0) {
                return;
            }

            items.each(function () {
                const item = $(this);
                const itemJk = item.data('jk').toString();
                const itemName = item.data('name');
                const itemDivisi = item.data('divisi');
                const itemStatus = item.data('status');

                // Check Gender Filter
                const matchJk = (currentFilterJk === 'all') || (itemJk === currentFilterJk);

                // Check Search Query
                const matchSearch = (!query) || itemName.includes(query) || itemDivisi.includes(query);

                if (matchJk && matchSearch) {
                    item.show();
                    countVisible++;

                    if (itemStatus === 'hadir') countHadir++;
                    else if (itemStatus === 'sdc') countSDC++;
                    else if (itemStatus === 'izin') countIzin++;
                } else {
                    item.hide();
                }
            });

            // Update stats realtime sesuai filter
            $('#statHadir').text(countHadir);
            $('#statSDC').text(countSDC);
            $('#statIzin').text(countIzin);
            $('#statTotal').text(countVisible);
            $('#visibleCount').text(countVisible);

            // Tampilkan No Match Notice jika pencarian kosong
            if (countVisible === 0) {
                $('#noMatchNotice').show();
            } else {
                $('#noMatchNotice').hide();
            }
        }
    </script>
</body>
</html>

