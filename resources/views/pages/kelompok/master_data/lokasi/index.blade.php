@extends('layouts.app')

@section('content')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
<style>
    #mapLokasi {
        height: 340px;
        width: 100%;
        border-radius: 6px;
        border: 1px solid #ced4da;
        z-index: 1;
    }
    .leaflet-container {
        font-family: inherit;
    }
</style>

<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">Data Lokasi</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item">Data Kelompok</li>
                    <li class="breadcrumb-item">Master Data</li>
                    <li class="breadcrumb-item active"><a href="{{ route('kelompok.master_data.lokasi.index') }}">Data Lokasi</a></li>
                </ol>
            </div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card card-outline card-success">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-map-marker-alt mr-1"></i>
                            Master Data Lokasi & Titik Koordinat Absensi
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <button type="button" class="btn btn-success btn-sm" data-toggle="modal" data-target="#modalInput" id="tambahData">
                                <i class="fa fa-plus mr-1"></i> Tambah Lokasi
                            </button>
                        </div>

                        <div class="table-responsive">
                            <table id="dataTables" class="table table-bordered table-striped table-hover">
                                <thead>
                                    <tr class="text-center">
                                        <th style="width: 5%;">No</th>
                                        <th>Nama Lokasi</th>
                                        <th>Alamat</th>
                                        <th style="width: 25%;">Titik & Radius Absensi</th>
                                        <th style="width: 10%;">Status</th>
                                        <th style="width: 12%;">Diperbarui</th>
                                        <th style="width: 18%;">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($datas as $key => $data)
                                        <tr>
                                            <td class="text-center align-middle">{{ ++$key }}</td>
                                            <td class="align-middle font-weight-bold">{{ $data->nama }}</td>
                                            <td class="align-middle text-muted">{{ $data->alamat ?? '-' }}</td>
                                            <td class="align-middle small">
                                                @if(!empty($data->latitude) && !empty($data->longitude))
                                                    <div class="d-flex flex-column">
                                                        <span><i class="fas fa-map-pin text-danger mr-1"></i> Lat: <code>{{ $data->latitude }}</code></span>
                                                        <span><i class="fas fa-map-pin text-primary mr-1"></i> Long: <code>{{ $data->longitude }}</code></span>
                                                        <div class="mt-1 d-flex align-items-center">
                                                            <span class="badge badge-info mr-1">
                                                                <i class="fas fa-bullseye mr-1"></i> Radius: {{ $data->radius ?? 100 }}m
                                                            </span>
                                                            <a href="https://maps.google.com/?q={{ $data->latitude }},{{ $data->longitude }}" target="_blank" class="badge badge-light border" title="Buka di Google Maps">
                                                                <i class="fas fa-external-link-alt text-primary"></i> Maps
                                                            </a>
                                                        </div>
                                                    </div>
                                                @else
                                                    <span class="text-muted font-italic"><i class="fas fa-exclamation-circle text-warning mr-1"></i> Belum diset</span>
                                                @endif
                                            </td>
                                            <td class="text-center align-middle">
                                                @if($data->status == 1)
                                                    <span class="badge badge-success px-2 py-1"><i class="fas fa-check-circle mr-1"></i> Active</span>
                                                @else
                                                    <span class="badge badge-secondary px-2 py-1"><i class="fas fa-ban mr-1"></i> Non Active</span>
                                                @endif
                                            </td>
                                            <td class="text-center align-middle font-italic small">{{ date("d-m-Y H:i", strtotime($data->updated_at)) }}</td>
                                            <td class="text-center align-middle">
                                                <div class="btn-group" role="group">
                                                    <button type="button" class="btn btn-warning btn-sm update-data"
                                                        data-toggle="modal" data-target="#modalInput"
                                                        data-id="{{ $data->id }}"
                                                        data-nama="{{ $data->nama }}"
                                                        data-alamat="{{ $data->alamat }}"
                                                        data-latitude="{{ $data->latitude }}"
                                                        data-longitude="{{ $data->longitude }}"
                                                        data-radius="{{ $data->radius ?? 100 }}"
                                                        data-status="{{ $data->status }}">
                                                        <i class="far fa-edit"></i> Ubah
                                                    </button>
                                                    @if($data->status == 1)
                                                        <button type="button" class="btn btn-outline-danger btn-sm toggle-status"
                                                            data-id="{{ $data->id }}"
                                                            data-nama="{{ $data->nama }}"
                                                            data-next-action="menonaktifkan">
                                                            <i class="fas fa-toggle-on"></i> Nonaktifkan
                                                        </button>
                                                    @else
                                                        <button type="button" class="btn btn-outline-success btn-sm toggle-status"
                                                            data-id="{{ $data->id }}"
                                                            data-nama="{{ $data->nama }}"
                                                            data-next-action="mengaktifkan">
                                                            <i class="fas fa-toggle-off"></i> Aktifkan
                                                        </button>
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="7" class="text-center text-muted">Belum ada data lokasi.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Modal Input / Edit -->
<div class="modal fade" data-keyboard="false" data-backdrop="static" id="modalInput" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="overlay loading" style="display: none;">
                <div class="d-flex justify-content-center">
                    <div class="spinner-border text-primary" style="width: 3rem; height: 3rem;" role="status">
                        <span class="sr-only">Loading...</span>
                    </div>
                </div>
            </div>
            <div class="modal-header bg-light">
                <h5 class="modal-title font-weight-bold"><span id="title"></span> Data Lokasi & Titik Absensi</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form name="input-data" method="POST" action="{{ route('kelompok.master_data.lokasi.create') }}" onsubmit="return validateForm()">
                @csrf
                <div class="modal-body">
                    <input type="hidden" id="id" name="id">

                    <div class="row">
                        <div class="col-md-6 form-group">
                            <label for="nama">Nama Lokasi <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" placeholder="Contoh: Masjid Baitul Makmur" id="nama" name="nama" required>
                            <small class="form-text text-danger error-nama" style="display: none;">Harap isi nama lokasi!</small>
                        </div>
                        <div class="col-md-6 form-group">
                            <label for="status">Status</label>
                            <select class="form-control" id="status" name="status">
                                <option value="1">Active (Aktif)</option>
                                <option value="0">Non Active (Nonaktif)</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="alamat">Alamat Lokasi</label>
                        <input type="text" class="form-control" id="alamat" name="alamat" placeholder="Alamat lengkap (nama jalan, RT/RW, kelurahan)">
                    </div>

                    <hr class="my-3">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h6 class="font-weight-bold text-success mb-0">
                            <i class="fas fa-map-marked-alt mr-1"></i> Titik Geofencing Absensi
                        </h6>
                        <button type="button" class="btn btn-outline-primary btn-sm" id="btnGetLocation">
                            <i class="fas fa-crosshairs mr-1"></i> Gunakan Lokasi Saya Saat Ini
                        </button>
                    </div>

                    <p class="text-muted small mb-2">
                        <i class="fas fa-info-circle mr-1 text-info"></i> Klik pada peta atau geser pin merah untuk menandai titik. Lingkaran biru menandakan jangkauan radius kehadiran absensi.
                    </p>

                    <!-- Peta Interaktif Leaflet -->
                    <div id="mapLokasi" class="mb-3"></div>

                    <div class="row">
                        <div class="col-md-4 form-group">
                            <label for="latitude">Latitude (Lintang)</label>
                            <input type="number" step="any" class="form-control" id="latitude" name="latitude" placeholder="Contoh: -6.175392">
                        </div>
                        <div class="col-md-4 form-group">
                            <label for="longitude">Longitude (Bujur)</label>
                            <input type="number" step="any" class="form-control" id="longitude" name="longitude" placeholder="Contoh: 106.827153">
                        </div>
                        <div class="col-md-4 form-group">
                            <label for="radius">Radius Jarak (Meter)</label>
                            <div class="input-group">
                                <input type="number" min="1" max="50000" class="form-control" id="radius" name="radius" value="100">
                                <div class="input-group-append">
                                    <span class="input-group-text">meter</span>
                                </div>
                            </div>
                            <small class="form-text text-muted">Toleransi jarak absensi (default: 100m).</small>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <i class="fas fa-times mr-1"></i> Batal
                    </button>
                    <button type="submit" class="btn btn-primary" id="btnSave">
                        <i class="fas fa-check mr-1"></i> Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('js')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
<script>
    let map = null;
    let marker = null;
    let radiusCircle = null;
    const defaultLat = -6.1753924;
    const defaultLng = 106.8271528;
    const defaultRadius = 100;

    $(document).ready(function () {
        $('.loading').hide();
        $('.error-nama').hide();

        initMap();
    });

    function initMap() {
        if (map !== null) return;

        map = L.map('mapLokasi').setView([defaultLat, defaultLng], 15);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
        }).addTo(map);

        marker = L.marker([defaultLat, defaultLng], {
            draggable: true
        }).addTo(map);

        radiusCircle = L.circle([defaultLat, defaultLng], {
            radius: defaultRadius,
            color: '#007bff',
            fillColor: '#007bff',
            fillOpacity: 0.18,
            weight: 2
        }).addTo(map);

        // Event: Marker dragged
        marker.on('dragend', function (e) {
            const position = marker.getLatLng();
            updateFormCoordinates(position.lat, position.lng);
            radiusCircle.setLatLng(position);
        });

        // Event: Map clicked
        map.on('click', function (e) {
            marker.setLatLng(e.latlng);
            radiusCircle.setLatLng(e.latlng);
            updateFormCoordinates(e.latlng.lat, e.latlng.lng);
        });
    }

    function updateFormCoordinates(lat, lng) {
        $('#latitude').val(lat.toFixed(8));
        $('#longitude').val(lng.toFixed(8));
    }

    function setMapPosition(lat, lng, radius) {
        if (!lat || !lng || isNaN(lat) || isNaN(lng)) {
            lat = defaultLat;
            lng = defaultLng;
        }
        if (!radius || isNaN(radius)) {
            radius = defaultRadius;
        }

        const latLng = L.latLng(lat, lng);
        if (marker && radiusCircle && map) {
            marker.setLatLng(latLng);
            radiusCircle.setLatLng(latLng);
            radiusCircle.setRadius(radius);
            map.setView(latLng, 16);
        }
    }

    // Modal shown event -> resize map to avoid grey tiles
    $('#modalInput').on('shown.bs.modal', function () {
        setTimeout(function () {
            if (map) {
                map.invalidateSize();
                const lat = parseFloat($('#latitude').val()) || defaultLat;
                const lng = parseFloat($('#longitude').val()) || defaultLng;
                const rad = parseInt($('#radius').val()) || defaultRadius;
                setMapPosition(lat, lng, rad);
            }
        }, 200);
    });

    // Update circle when radius input changes
    $('#radius').on('input change', function () {
        const rad = parseInt($(this).val()) || defaultRadius;
        if (radiusCircle) {
            radiusCircle.setRadius(rad);
        }
    });

    // Update marker when manual lat/long input changes
    $('#latitude, #longitude').on('input change', function () {
        const lat = parseFloat($('#latitude').val());
        const lng = parseFloat($('#longitude').val());
        const rad = parseInt($('#radius').val()) || defaultRadius;

        if (!isNaN(lat) && !isNaN(lng)) {
            setMapPosition(lat, lng, rad);
        }
    });

    // Get current location via browser Geolocation API
    $('#btnGetLocation').on('click', function () {
        if (!navigator.geolocation) {
            toastMixin.fire({
                icon: 'error',
                title: 'Perangkat Anda tidak mendukung fitur Geolocation.',
            });
            return;
        }

        const $btn = $(this);
        $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i> Mendeteksi Lokasi...');

        navigator.geolocation.getCurrentPosition(
            function (position) {
                const lat = position.coords.latitude;
                const lng = position.coords.longitude;
                const rad = parseInt($('#radius').val()) || defaultRadius;

                updateFormCoordinates(lat, lng);
                setMapPosition(lat, lng, rad);

                $btn.prop('disabled', false).html('<i class="fas fa-crosshairs mr-1"></i> Gunakan Lokasi Saya Saat Ini');
                toastMixin.fire({
                    icon: 'success',
                    title: 'Lokasi Anda berhasil dideteksi!',
                });
            },
            function (error) {
                $btn.prop('disabled', false).html('<i class="fas fa-crosshairs mr-1"></i> Gunakan Lokasi Saya Saat Ini');
                let message = 'Gagal mendeteksi lokasi.';
                if (error.code === error.PERMISSION_DENIED) {
                    message = 'Izin akses lokasi ditolak oleh pengguna.';
                } else if (error.code === error.POSITION_UNAVAILABLE) {
                    message = 'Informasi lokasi tidak tersedia.';
                } else if (error.code === error.TIMEOUT) {
                    message = 'Waktu permintaan lokasi habis.';
                }
                toastMixin.fire({
                    icon: 'error',
                    title: message,
                });
            },
            {
                enableHighAccuracy: true,
                timeout: 10000,
                maximumAge: 0
            }
        );
    });

    function validateForm() {
        $('.loading').show();
        let isValid = true;

        const nama = $('#nama').val();
        if (!nama || nama.trim() === '') {
            $('.error-nama').show();
            isValid = false;
        } else {
            $('.error-nama').hide();
        }

        if (!isValid) {
            $('.loading').hide();
        }

        return isValid;
    }

    $('#tambahData').on('click', function () {
        $('.loading').hide();

        $('#btnSave').html('<i class="fas fa-check mr-1"></i> Tambah');
        $('#title').text('Tambah');

        $('#id').val('');
        $('#nama').val('');
        $('#alamat').val('');
        $('#latitude').val('');
        $('#longitude').val('');
        $('#radius').val('100');
        $('#status').val('1');

        $('.error-nama').hide();

        setMapPosition(defaultLat, defaultLng, defaultRadius);
    });

    $(document).on("click", ".update-data", function () {
        $('.loading').hide();

        $('#btnSave').html('<i class="fas fa-check mr-1"></i> Perbarui');
        $('#title').text('Ubah');

        const id = $(this).data('id');
        const nama = $(this).data('nama');
        const alamat = $(this).data('alamat');
        const latitude = $(this).data('latitude');
        const longitude = $(this).data('longitude');
        const radius = $(this).data('radius') || 100;
        const status = $(this).data('status');

        $('#id').val(id);
        $('#nama').val(nama);
        $('#alamat').val(alamat);
        $('#latitude').val(latitude || '');
        $('#longitude').val(longitude || '');
        $('#radius').val(radius);
        $('#status').val(status);

        $('.error-nama').hide();

        const latVal = parseFloat(latitude) || defaultLat;
        const lngVal = parseFloat(longitude) || defaultLng;
        setMapPosition(latVal, lngVal, parseInt(radius));
    });

    $(document).on('click', '.toggle-status', function(e) {
        e.preventDefault();

        const id = $(this).data('id');
        const nama = $(this).data('nama');
        const actionText = $(this).data('next-action');

        Swal.fire({
            title: "Ubah Status Lokasi?",
            text: "Apakah Anda yakin ingin " + actionText + " data lokasi \"" + nama + "\"?",
            icon: "question",
            showCancelButton: true,
            confirmButtonColor: "#28a745",
            cancelButtonColor: "#6c757d",
            confirmButtonText: '<i class="fas fa-check"></i> Ya, Lanjutkan',
            cancelButtonText: '<i class="fas fa-times"></i> Batal',
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    type: "POST",
                    url: "{{ url('/kelompok/master-data/lokasi/toggle-status') }}/" + id,
                    data: {
                        _token: "{{ csrf_token() }}"
                    },
                    success: function(data) {
                        if (data.status === "success") {
                            toastMixin.fire({
                                icon: 'success',
                                title: data.message || ('Status lokasi ' + nama + ' berhasil diperbarui.'),
                            });
                            location.reload();
                        } else {
                            toastMixin.fire({
                                icon: 'error',
                                title: data.message || ('Gagal mengubah status lokasi ' + nama),
                            });
                        }
                    },
                    error: function() {
                        toastMixin.fire({
                            icon: 'error',
                            title: 'Terjadi kesalahan pada sistem.',
                        });
                    }
                });
            }
        });
    });
</script>
@endsection
