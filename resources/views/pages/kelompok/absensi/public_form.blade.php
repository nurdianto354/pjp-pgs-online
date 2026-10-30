<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Presensi Kegiatan - {{ $jadwal->getKegiatan ? $jadwal->getKegiatan->nama : 'Kelompok' }}</title>

    <!-- Bootstrap 4 CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <!-- Google Font: Inter / Poppins -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Select2 -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@ttskch/select2-bootstrap4-theme@1.5.2/dist/select2-bootstrap4.min.css">

    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #e8f5e9 0%, #f1f8e9 50%, #e0f2f1 100%);
            min-height: 100vh;
            color: #2c3e50;
            padding-bottom: 40px;
        }
        .main-card {
            border: none;
            border-radius: 16px;
            box-shadow: 0 12px 35px rgba(0,0,0,0.08);
            overflow: hidden;
            background: #ffffff;
        }
        .card-header-gradient {
            background: linear-gradient(135deg, #1b5e20 0%, #2e7d32 100%);
            color: #ffffff;
            padding: 24px 20px;
            text-align: center;
        }
        .status-option-card {
            border: 2px solid #e9ecef;
            border-radius: 12px;
            padding: 14px 10px;
            text-align: center;
            cursor: pointer;
            transition: all 0.25s ease-in-out;
            background: #ffffff;
        }
        .status-option-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.08);
        }
        .status-option-card.selected-hadir {
            border-color: #28a745;
            background-color: #e8f5e9;
            color: #1b5e20;
            font-weight: 600;
        }
        .status-option-card.selected-sdc {
            border-color: #007bff;
            background-color: #e3f2fd;
            color: #0d47a1;
            font-weight: 600;
        }
        .status-option-card.selected-izin {
            border-color: #ffc107;
            background-color: #fffde7;
            color: #f57f17;
            font-weight: 600;
        }
        .status-icon {
            font-size: 26px;
            margin-bottom: 6px;
            display: block;
        }
        .btn-submit {
            background: linear-gradient(135deg, #2e7d32 0%, #1b5e20 100%);
            color: #ffffff;
            border: none;
            font-weight: 600;
            border-radius: 10px;
            padding: 12px 20px;
            font-size: 16px;
            letter-spacing: 0.3px;
        }
        .btn-submit:hover {
            background: linear-gradient(135deg, #1b5e20 0%, #004d40 100%);
            color: #ffffff;
        }
        .badge-tipe-turba {
            background: #e3f2fd;
            color: #1565c0;
            border: 1px solid #90caf9;
        }
        .badge-tipe-total {
            background: #fff3e0;
            color: #e65100;
            border: 1px solid #ffcc80;
        }
        .gps-status-box {
            border-radius: 10px;
            padding: 10px 14px;
            font-size: 13px;
        }
    </style>
</head>
<body>
    <div class="container py-3 py-md-4">
        <div class="row justify-content-center">
            <div class="col-12 col-md-8 col-lg-6">
                <div class="main-card">
                    <!-- Header Info Acara -->
                    <div class="card-header-gradient">
                        <span class="badge badge-light px-3 py-1 mb-2 text-dark font-weight-bold" style="border-radius: 20px;">
                            <i class="fas fa-calendar-check text-success mr-1"></i> Form Presensi Kegiatan
                        </span>
                        <h4 class="font-weight-bold mb-1">
                            {{ $jadwal->getKegiatan ? $jadwal->getKegiatan->nama : 'Jadwal Kegiatan' }}
                        </h4>
                        <div class="mt-2 small" style="opacity: 0.95;">
                            @if($jadwal->tipe == 'turba')
                                <span class="badge badge-tipe-turba px-2 py-1 mr-1">
                                    <i class="fas fa-route mr-1"></i> TURBA (Multi-Lokasi)
                                </span>
                            @else
                                <span class="badge badge-tipe-total px-2 py-1 mr-1">
                                    <i class="fas fa-bullseye mr-1"></i> TOTAL (Terpusat)
                                </span>
                            @endif
                            <span>
                                <i class="far fa-calendar-alt ml-1 mr-1"></i>
                                {{ $jadwal->tanggal ? $jadwal->tanggal->format('d M Y') : '-' }}
                            </span>
                            <span>
                                <i class="far fa-clock ml-1 mr-1"></i>
                                {{ substr($jadwal->jam_mulai, 0, 5) }} - {{ substr($jadwal->jam_selesai, 0, 5) }} WIB
                            </span>
                        </div>
                    </div>

                    <div class="card-body p-3 p-md-4">
                        <!-- Info Lokasi Kegiatan yang Sah -->
                        <div class="alert alert-light border p-2 mb-3 small">
                            <strong class="d-block text-dark mb-1">
                                <i class="fas fa-map-marked-alt text-success mr-1"></i> Lokasi Pelaksanaan:
                            </strong>
                            <div class="d-flex flex-wrap gap-1">
                                @forelse($jadwal->getLokasi as $lokasi)
                                    <span class="badge badge-light border text-secondary px-2 py-1 mr-1 mb-1">
                                        <i class="fas fa-map-pin text-danger mr-1"></i> {{ $lokasi->nama }}
                                        <small class="text-muted">(Radius: {{ $lokasi->radius ?? 100 }}m)</small>
                                    </span>
                                @empty
                                    <span class="text-muted font-italic">- Semua Lokasi -</span>
                                @endforelse
                            </div>
                        </div>

                        <!-- Tombol Akses Rekap Kehadiran Publik (Tanpa Login) -->
                        <div class="mb-3">
                            <a href="{{ route('kelompok.absensi.public_rekap', $jadwal->id) }}" class="btn btn-outline-primary btn-block shadow-sm font-weight-bold py-2" style="border-radius: 8px;">
                                <i class="fas fa-list-alt mr-1"></i> Lihat Rekap Kehadiran Kegiatan
                            </a>
                        </div>

                        <!-- Form Input Presensi -->
                        <form id="formPresensi" method="POST" action="{{ route('kelompok.absensi.public_store', $jadwal->id) }}">
                            @csrf
                            <input type="hidden" name="latitude" id="latitude">
                            <input type="hidden" name="longitude" id="longitude">
                            <input type="hidden" name="status" id="selectedStatus" value="hadir">

                            <!-- Pilihan Jenis Kelamin -->
                            <div class="form-group mb-3">
                                <label class="font-weight-bold text-dark small d-block">
                                    <i class="fas fa-venus-mars text-primary mr-1"></i> 1. Pilih Jenis Kelamin: <span class="text-danger">*</span>
                                </label>
                                <div class="row no-gutters">
                                    <div class="col-6 pr-1">
                                        <div class="status-option-card selected-hadir jk-option-card" data-jk="1" id="cardJkLaki">
                                            <i class="fas fa-mars status-icon text-info"></i>
                                            <div class="font-weight-bold small">Laki-laki</div>
                                        </div>
                                    </div>
                                    <div class="col-6 pl-1">
                                        <div class="status-option-card jk-option-card" data-jk="0" id="cardJkPerempuan">
                                            <i class="fas fa-venus status-icon text-danger"></i>
                                            <div class="font-weight-bold small">Perempuan</div>
                                        </div>
                                    </div>
                                </div>
                                <input type="hidden" id="selectedJk" value="1">
                                <small class="form-text text-muted mt-2">
                                    <i class="fas fa-filter text-info mr-1"></i> Daftar nama jamaah di bawah otomatis menyesuaikan jenis kelamin yang dipilih.
                                </small>
                            </div>

                            <!-- Pilihan Nama Jamaah -->
                            <div class="form-group mb-3">
                                <label for="jamaah_id" class="font-weight-bold text-dark small">
                                    <i class="fas fa-user-check text-success mr-1"></i> 2. Nama Lengkap Jamaah: <span class="text-danger">*</span>
                                </label>
                                <select class="form-control select2" id="jamaah_id" name="jamaah_id" required style="width: 100%;">
                                    <option value="">-- Memuat daftar jamaah... --</option>
                                </select>
                                <small class="form-text text-muted" id="jamaahLoadedCount">
                                    Ketik nama untuk mencari secara cepat.
                                </small>
                            </div>

                            <!-- Pilihan Status Kehadiran (3 Opsi: Hadir, SDC, Izin) -->
                            <div class="form-group mb-3">
                                <label class="font-weight-bold text-dark small d-block">
                                    <i class="fas fa-clipboard-list text-info mr-1"></i> 3. Status Kehadiran: <span class="text-danger">*</span>
                                </label>
                                <div class="row no-gutters">
                                    <!-- Hadir -->
                                    <div class="col-4 pr-1">
                                        <div class="status-option-card selected-hadir" data-status="hadir" id="cardHadir">
                                            <i class="fas fa-check-circle status-icon text-success"></i>
                                            <div class="small">Hadir</div>
                                        </div>
                                    </div>
                                    <!-- SDC -->
                                    <div class="col-4 px-1">
                                        <div class="status-option-card" data-status="sdc" id="cardSDC">
                                            <i class="fas fa-briefcase status-icon text-primary"></i>
                                            <div class="small">SDC</div>
                                        </div>
                                    </div>
                                    <!-- Izin -->
                                    <div class="col-4 pl-1">
                                        <div class="status-option-card" data-status="izin" id="cardIzin">
                                            <i class="fas fa-envelope-open-text status-icon text-warning"></i>
                                            <div class="small">Izin</div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Indikator Geofencing GPS (Hanya Aktif Jika Memilih Hadir) -->
                            <div id="gpsStatusContainer" class="mb-3">
                                <div class="gps-status-box bg-light border text-muted" id="gpsStatusBox">
                                    <div class="d-flex align-items-start justify-content-between">
                                        <div class="d-flex align-items-start pr-2">
                                            <i class="fas fa-satellite-dish text-primary mr-2 fa-lg fa-spin mt-1" id="gpsIcon"></i>
                                            <div>
                                                <strong class="d-block" id="gpsTitle">Mendeteksi Titik GPS...</strong>
                                                <span class="small d-block" id="gpsDesc">Harap aktifkan GPS & izinkan akses lokasi di browser.</span>
                                            </div>
                                        </div>
                                        <button type="button" class="btn btn-sm btn-outline-secondary ml-1" id="btnRetryGps" title="Deteksi Ulang Lokasi" style="white-space: nowrap;">
                                            <i class="fas fa-redo-alt mr-1"></i> Ulang
                                        </button>
                                    </div>
                                    <div id="gpsGuideBox" class="mt-2 pt-2 border-top small" style="display: none;">
                                        <a href="javascript:void(0)" id="btnShowGpsGuide" class="text-danger font-weight-bold">
                                            <i class="fas fa-question-circle mr-1"></i> Panduan: Cara Mengaktifkan Izin Lokasi di Browser
                                        </a>
                                    </div>
                                </div>
                            </div>

                            <!-- Kolom Keterangan (Wajib Jika SDC atau Izin) -->
                            <div class="form-group mb-3" id="keteranganContainer" style="display: none;">
                                <label for="keterangan" class="font-weight-bold text-dark small" id="labelKeterangan">
                                    <i class="fas fa-edit text-warning mr-1"></i> Keterangan / Alasan: <span class="text-danger">*</span>
                                </label>
                                <textarea class="form-control" id="keterangan" name="keterangan" rows="2" placeholder="Wajib diisi alasan dinas/izin..."></textarea>
                                <small class="form-text text-danger font-italic" id="keteranganRequiredText" style="display: none;">
                                    * Wajib isi keterangan alasan untuk status ini!
                                </small>
                            </div>

                            <!-- Tombol Submit -->
                            <button type="submit" class="btn btn-submit btn-block shadow-sm" id="btnSubmit">
                                <i class="fas fa-paper-plane mr-1"></i> Kirim Presensi
                            </button>
                        </form>
                    </div>

                    <div class="card-footer bg-light text-center py-2 text-muted small">
                        PJP Online &copy; {{ date('Y') }} - Presensi Kegiatan Kelompok
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Script jQuery, Bootstrap, SweetAlert2, Select2 -->
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.5.1/dist/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <script>
        const JADWAL_LOCATIONS = @json($jadwal->getLokasi);
        let userLatitude = null;
        let userLongitude = null;
        let isGpsAcquired = false;
        let currentJk = 1; // Default Laki-laki

        $(document).ready(function () {
            // Inisialisasi Select2
            $('#jamaah_id').select2({
                theme: 'bootstrap4',
                placeholder: "-- Pilih atau Cari Nama Anda --",
                allowClear: true
            });

            // Muat daftar jamaah default Laki-laki
            loadJamaahList(currentJk);

            // Handler ganti Jenis Kelamin
            $('.jk-option-card').on('click', function () {
                const jk = $(this).data('jk');
                currentJk = jk;
                $('#selectedJk').val(jk);

                $('.jk-option-card').removeClass('selected-hadir');
                $(this).addClass('selected-hadir');

                // Muat ulang daftar jamaah sesuai jenis kelamin
                loadJamaahList(jk);
            });

            // Ambil GPS jika status default adalah Hadir
            requestGpsLocation();
        });

        // Request Koordinat GPS Perangkat dengan Fallback Otomatis
        function requestGpsLocation(useHighAccuracy = true) {
            if (!navigator.geolocation) {
                updateGpsUI('unsupported', 'Perangkat / browser Anda tidak mendukung fitur Geolocation.');
                return;
            }

            // Pengecekan Secure Context (HTTPS)
            if (window.isSecureContext === false && location.hostname !== 'localhost' && location.hostname !== '127.0.0.1') {
                updateGpsUI('error', 'Browser memblokir fitur lokasi pada domain HTTP. Harap akses melalui HTTPS atau http://localhost.');
                $('#gpsGuideBox').show();
                return;
            }

            $('#gpsIcon').addClass('fa-spin text-primary').removeClass('fa-check-circle fa-exclamation-triangle text-success text-danger text-warning');
            $('#gpsTitle').text('Mendeteksi Lokasi GPS Anda...');
            $('#gpsDesc').text('Mohon izinkan saat muncul permintaan izin lokasi browser.');
            $('#btnRetryGps').prop('disabled', true);

            navigator.geolocation.getCurrentPosition(
                function (position) {
                    $('#btnRetryGps').prop('disabled', false);
                    userLatitude = position.coords.latitude;
                    userLongitude = position.coords.longitude;
                    isGpsAcquired = true;

                    $('#latitude').val(userLatitude);
                    $('#longitude').val(userLongitude);

                    evaluateGeofencing(userLatitude, userLongitude);
                },
                function (error) {
                    // Jika percobaan highAccuracy gagal karena timeout/unavailable, coba fallback ke standard accuracy
                    if (useHighAccuracy && (error.code === error.TIMEOUT || error.code === error.POSITION_UNAVAILABLE)) {
                        console.warn('GPS High accuracy timeout, mencoba fallback standard accuracy...');
                        requestGpsLocation(false);
                        return;
                    }

                    $('#btnRetryGps').prop('disabled', false);
                    isGpsAcquired = false;
                    let msg = 'Izin GPS ditolak atau tidak aktif.';

                    if (error.code === error.PERMISSION_DENIED) {
                        msg = 'Akses lokasi diblokir oleh browser. Harap izinkan akses lokasi melalui setelan browser (ikon gembok/setelan situs).';
                        $('#gpsGuideBox').show();
                    } else if (error.code === error.POSITION_UNAVAILABLE) {
                        msg = 'Sinyal / informasi lokasi GPS tidak tersedia. Pastikan Location Service HP Anda aktif.';
                    } else if (error.code === error.TIMEOUT) {
                        msg = 'Waktu deteksi GPS habis. Silakan klik tombol Ulang di samping.';
                    }
                    updateGpsUI('error', msg);
                },
                {
                    enableHighAccuracy: useHighAccuracy,
                    timeout: useHighAccuracy ? 7000 : 12000,
                    maximumAge: 30000
                }
            );
        }

        // Tombol Coba Ulang Deteksi GPS
        $(document).on('click', '#btnRetryGps', function () {
            requestGpsLocation(true);
        });

        // Tombol Panduan Cara Mengaktifkan Izin Lokasi
        $(document).on('click', '#btnShowGpsGuide', function () {
            Swal.fire({
                title: 'Cara Mengaktifkan Izin Lokasi',
                html: `
                    <div class="text-left small">
                        <h6 class="font-weight-bold text-success"><i class="fab fa-chrome mr-1"></i> Jika di Google Chrome (HP Android):</h6>
                        <ol class="pl-3 mb-3">
                            <li>Ketuk ikon <b>Gembok / Setelan Situs</b> di sebelah kiri bilah URL atas (alamat web).</li>
                            <li>Pilih menu <b>Izin (Permissions)</b>.</li>
                            <li>Ketuk <b>Lokasi (Location)</b>, lalu ubah ke <b>Izinkan (Allow)</b>.</li>
                            <li>Tutup setelan, lalu ketuk tombol <b>Ulang</b> di halaman presensi.</li>
                        </ol>

                        <h6 class="font-weight-bold text-primary"><i class="fab fa-chrome mr-1"></i> Jika di Komputer / Laptop (Chrome / Edge):</h6>
                        <ol class="pl-3 mb-2">
                            <li>Klik ikon <b>Gembok / Tombol Setelan</b> di sebelah kiri URL.</li>
                            <li>Aktifkan toggle <b>Lokasi / Location</b> ke posisi <b>ON / Izinkan</b>.</li>
                            <li>Muat ulang halaman atau klik tombol <b>Ulang</b>.</li>
                        </ol>
                        
                        <div class="alert alert-info py-2 px-3 mt-2 mb-0">
                            <i class="fas fa-info-circle mr-1"></i> Pastikan juga GPS / Location di pengaturan perangkat HP Anda dalam kondisi <b>AKTIF</b>.
                        </div>
                    </div>
                `,
                icon: 'info',
                confirmButtonColor: '#28a745',
                confirmButtonText: 'Saya Paham, Coba Lagi'
            }).then(() => {
                requestGpsLocation(true);
            });
        });

        // Evaluasi Geofencing terhadap daftar lokasi jadwal
        function evaluateGeofencing(lat, lng) {
            if (!JADWAL_LOCATIONS || JADWAL_LOCATIONS.length === 0) {
                updateGpsUI('success', 'Lokasi GPS terdeteksi (' + lat.toFixed(5) + ', ' + lng.toFixed(5) + ')');
                return;
            }

            let inRadius = false;
            let closestDist = Infinity;
            let closestLocName = '';
            let closestRadius = 100;

            JADWAL_LOCATIONS.forEach(function (loc) {
                if (loc.latitude && loc.longitude) {
                    const dist = calculateDistance(lat, lng, parseFloat(loc.latitude), parseFloat(loc.longitude));
                    const radius = parseInt(loc.radius) || 100;

                    if (dist < closestDist) {
                        closestDist = dist;
                        closestLocName = loc.nama;
                        closestRadius = radius;
                    }

                    if (dist <= radius) {
                        inRadius = true;
                    }
                }
            });

            if (inRadius) {
                updateGpsUI('success', 'Posisi Anda valid berada di area kegiatan (' + closestLocName + ' - ' + closestDist + 'm)');
            } else {
                updateGpsUI('outside', 'Peringatan: Anda berada ' + closestDist + 'm dari ' + closestLocName + ' (Batas radius: ' + closestRadius + 'm)');
            }
        }

        function updateGpsUI(state, text) {
            const icon = $('#gpsIcon');
            const box = $('#gpsStatusBox');

            icon.removeClass('fa-spin');

            if (state === 'success') {
                icon.removeClass('fa-satellite-dish fa-exclamation-triangle text-danger text-primary text-warning')
                    .addClass('fa-check-circle text-success');
                box.removeClass('bg-light bg-warning bg-danger alert-warning alert-danger').addClass('alert-success border-success');
                $('#gpsTitle').text('GPS Terverifikasi');
                $('#gpsDesc').text(text);
                $('#gpsGuideBox').hide();
            } else if (state === 'outside') {
                icon.removeClass('fa-satellite-dish fa-check-circle text-success text-primary text-danger')
                    .addClass('fa-exclamation-triangle text-warning');
                box.removeClass('bg-light alert-success bg-danger alert-danger').addClass('alert-warning border-warning');
                $('#gpsTitle').text('Di Luar Radius Lokasi');
                $('#gpsDesc').text(text);
                $('#gpsGuideBox').hide();
            } else {
                icon.removeClass('fa-satellite-dish fa-check-circle text-success text-primary text-warning')
                    .addClass('fa-exclamation-triangle text-danger');
                box.removeClass('bg-light alert-success alert-warning').addClass('alert-danger border-danger');
                $('#gpsTitle').text('GPS Belum Aktif / Ditolak');
                $('#gpsDesc').text(text);
                $('#gpsGuideBox').show();
            }
        }

        // Rumus Haversine di Javascript
        function calculateDistance(lat1, lon1, lat2, lon2) {
            const R = 6371000;
            const dLat = (lat2 - lat1) * Math.PI / 180;
            const dLon = (lon2 - lon1) * Math.PI / 180;
            const a = Math.sin(dLat / 2) * Math.sin(dLat / 2) +
                      Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) *
                      Math.sin(dLon / 2) * Math.sin(dLon / 2);
            const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
            return Math.round(R * c);
        }

        // Pilihan Status Card (Hadir, SDC, Izin)
        $('.status-option-card').on('click', function () {
            const status = $(this).data('status');
            $('#selectedStatus').val(status);

            $('.status-option-card').removeClass('selected-hadir selected-sdc selected-izin');

            if (status === 'hadir') {
                $(this).addClass('selected-hadir');
                // Tampilkan info GPS
                $('#gpsStatusContainer').slideDown();
                // Keterangan opsional / disembunyikan
                $('#keteranganContainer').slideUp();
                $('#keterangan').prop('required', false);
                $('#keteranganRequiredText').hide();

                if (!isGpsAcquired) {
                    requestGpsLocation();
                }
            } else if (status === 'sdc') {
                $(this).addClass('selected-sdc');
                // SDC tidak butuh pengecekan GPS
                $('#gpsStatusContainer').slideUp();
                // Keterangan WAJIB
                $('#labelKeterangan').html('<i class="fas fa-edit text-primary mr-1"></i> Keperluan / Keterangan SDC: <span class="text-danger">*</span>');
                $('#keterangan').attr('placeholder', 'Wajib isi: tugas dinas / sambung kegiatan ke mana...');
                $('#keteranganContainer').slideDown();
                $('#keterangan').prop('required', true).focus();
                $('#keteranganRequiredText').show();
            } else if (status === 'izin') {
                $(this).addClass('selected-izin');
                // Izin tidak butuh pengecekan GPS
                $('#gpsStatusContainer').slideUp();
                // Keterangan WAJIB
                $('#labelKeterangan').html('<i class="fas fa-edit text-warning mr-1"></i> Alasan / Keterangan Izin: <span class="text-danger">*</span>');
                $('#keterangan').attr('placeholder', 'Wajib isi: alasan berhalangan hadir...');
                $('#keteranganContainer').slideDown();
                $('#keterangan').prop('required', true).focus();
                $('#keteranganRequiredText').show();
            }
        });

        // Load Jamaah List via AJAX berdasarkan Jenis Kelamin
        function loadJamaahList(jk) {
            const select = $('#jamaah_id');
            select.empty().append('<option value="">Memuat data jamaah...</option>');

            $.ajax({
                url: "{{ route('kelompok.absensi.get_jamaah', $jadwal->id) }}",
                type: "GET",
                data: { jenis_kelamin: jk },
                success: function (res) {
                    select.empty();
                    const labelJk = jk == 1 ? 'Laki-laki' : 'Perempuan';
                    if (res.status === 'success' && res.data.length > 0) {
                        select.append('<option value="">-- Pilih atau Cari Nama Anda --</option>');
                        res.data.forEach(function (j) {
                            select.append(
                                `<option value="${j.id}">
                                    ${j.nama_lengkap} (Divisi: ${j.divisi})
                                </option>`
                            );
                        });
                        $('#jamaahLoadedCount').html('<i class="fas fa-info-circle text-info mr-1"></i> Tersedia <b>' + res.data.length + '</b> orang jamaah ' + labelJk + ' yang belum presensi.');
                    } else {
                        select.append('<option value="">-- Semua jamaah ' + labelJk + ' sudah presensi --</option>');
                        $('#jamaahLoadedCount').html('<span class="text-success font-weight-bold"><i class="fas fa-check-double mr-1"></i> Semua jamaah ' + labelJk + ' telah melakukan presensi!</span>');
                    }
                    select.trigger('change');
                },
                error: function () {
                    select.empty().append('<option value="">Gagal memuat daftar jamaah</option>');
                }
            });
        }

        // Submit Presensi
        $('#formPresensi').on('submit', function (e) {
            e.preventDefault();

            const jamaahId = $('#jamaah_id').val();
            const status = $('#selectedStatus').val();
            const keterangan = $('#keterangan').val().trim();

            if (!jamaahId) {
                Swal.fire('Perhatian', 'Harap pilih nama jamaah Anda terlebih dahulu!', 'warning');
                return;
            }

            // Validasi Keterangan Wajib untuk SDC & Izin
            if ((status === 'sdc' || status === 'izin') && !keterangan) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Keterangan Wajib Diisi',
                    text: 'Untuk status kehadiran ' + status.toUpperCase() + ', Anda wajib mengisi kolom keterangan/alasan!'
                });
                $('#keterangan').focus();
                return;
            }

            // Validasi GPS untuk status Hadir
            if (status === 'hadir' && (!userLatitude || !userLongitude)) {
                Swal.fire({
                    icon: 'error',
                    title: 'GPS Tidak Terdeteksi',
                    text: 'Presensi Hadir membutuhkan lokasi GPS. Silakan aktifkan GPS HP Anda dan muat ulang halaman!'
                });
                return;
            }

            const btn = $('#btnSubmit');
            btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i> Memproses Presensi...');

            $.ajax({
                url: $(this).attr('action'),
                type: "POST",
                data: $(this).serialize(),
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function (res) {
                    btn.prop('disabled', false).html('<i class="fas fa-paper-plane mr-1"></i> Kirim Presensi');
                    if (res.status === 'success') {
                        Swal.fire({
                            icon: 'success',
                            title: 'Presensi Berhasil!',
                            text: res.message,
                            showCancelButton: true,
                            confirmButtonColor: '#17a2b8',
                            cancelButtonColor: '#28a745',
                            confirmButtonText: '<i class="fas fa-list-alt mr-1"></i> Lihat Rekap Kehadiran',
                            cancelButtonText: '<i class="fas fa-user-plus mr-1"></i> Presensi Jamaah Lain'
                        }).then((result) => {
                            if (result.isConfirmed) {
                                window.location.href = "{{ route('kelompok.absensi.public_rekap', $jadwal->id) }}";
                            } else {
                                // Reset pilihan
                                $('#jamaah_id').val('').trigger('change');
                                $('#keterangan').val('');
                                // Refresh data jamaah untuk update daftar nama
                                loadJamaahList(currentJk);
                            }
                        });
                    } else {
                        Swal.fire('Gagal', res.message || 'Terjadi kesalahan.', 'error');
                    }
                },
                error: function (xhr) {
                    btn.prop('disabled', false).html('<i class="fas fa-paper-plane mr-1"></i> Kirim Presensi');
                    let msg = 'Terjadi kesalahan sistem saat mengirim data presensi.';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        msg = xhr.responseJSON.message;
                    }
                    Swal.fire({
                        icon: 'error',
                        title: 'Presensi Gagal',
                        text: msg
                    });
                }
            });
        });
    </script>
</body>
</html>

