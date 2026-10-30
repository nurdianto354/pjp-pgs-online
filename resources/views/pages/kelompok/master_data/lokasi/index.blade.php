@extends('layouts.app')

@section('content')
@php
    $googleMapsKey = config('services.google.maps_api_key');
@endphp

@if(empty($googleMapsKey))
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
@endif

<style>
    #mapLokasi {
        height: 360px;
        width: 100%;
        border-radius: 6px;
        border: 1px solid #ced4da;
        z-index: 1;
    }
    .leaflet-container {
        font-family: inherit;
    }
    /* Pastikan dropdown autocomplete Google Places muncul di atas modal Bootstrap */
    .pac-container {
        z-index: 1060 !important;
        border-radius: 6px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.2);
    }
    #searchResultsList {
        position: absolute;
        width: 100%;
        z-index: 1055;
        max-height: 220px;
        overflow-y: auto;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    }
    #searchResultsList .list-group-item:hover {
        background-color: #f8f9fa;
        cursor: pointer;
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
                                        <th style="width: 15%;">Kelompok</th>
                                        <th>Alamat</th>
                                        <th style="width: 22%;">Titik & Radius Absensi</th>
                                        <th style="width: 10%;">Status</th>
                                        <th style="width: 12%;">Diperbarui</th>
                                        <th style="width: 16%;">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($datas as $key => $data)
                                        <tr>
                                            <td class="text-center align-middle">{{ ++$key }}</td>
                                            <td class="align-middle font-weight-bold">{{ $data->nama }}</td>
                                            <td class="align-middle">
                                                @if(!empty($data->getKelompok))
                                                    <span class="badge badge-primary px-2 py-1"><i class="fas fa-users mr-1"></i> {{ $data->getKelompok->nama }}</span>
                                                @else
                                                    <span class="badge badge-light border text-muted px-2 py-1">Umum</span>
                                                @endif
                                            </td>
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
                                                        data-kelompok-id="{{ $data->kelompok_id }}"
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
                <h5 class="modal-title font-weight-bold">
                    <span id="title"></span> Data Lokasi & Titik Absensi
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form name="input-data" method="POST" action="{{ route('kelompok.master_data.lokasi.create') }}" onsubmit="return validateForm()">
                @csrf
                <div class="modal-body">
                    <input type="hidden" id="id" name="id">

                    <div class="row">
                        <div class="col-md-5 form-group">
                            <label for="nama">Nama Lokasi <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" placeholder="Contoh: Masjid Baitul Makmur" id="nama" name="nama" required>
                            <small class="form-text text-danger error-nama" style="display: none;">Harap isi nama lokasi!</small>
                        </div>
                        <div class="col-md-4 form-group">
                            <label for="kelompok_id">Kelompok</label>
                            <select class="form-control" id="kelompok_id" name="kelompok_id">
                                <option value="">-- Pilih Kelompok (Opsional) --</option>
                                @foreach($listKelompok as $kelompok)
                                    <option value="{{ $kelompok->id }}">{{ $kelompok->nama }}</option>
                                @endforeach
                            </select>
                            <small class="form-text text-muted">Kelompok naungan lokasi.</small>
                        </div>
                        <div class="col-md-3 form-group">
                            <label for="status">Status</label>
                            <select class="form-control" id="status" name="status">
                                <option value="1">Active (Aktif)</option>
                                <option value="0">Non Active (Nonaktif)</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <label for="alamat" class="mb-0">Alamat Lokasi</label>
                            <span id="alamatLoading" class="text-primary small font-italic" style="display: none;">
                                <i class="fas fa-spinner fa-spin mr-1"></i> Mengambil alamat otomatis...
                            </span>
                        </div>
                        <textarea class="form-control" id="alamat" name="alamat" rows="2" placeholder="Alamat lengkap - otomatis terisi saat memilih titik di peta"></textarea>
                        <small class="form-text text-muted">
                            <i class="fas fa-magic mr-1 text-info"></i> Alamat otomatis terisi sesuai pergeseran titik pin di peta, dan tetap dapat Anda edit manual.
                        </small>
                    </div>

                    <hr class="my-3">

                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h6 class="font-weight-bold text-success mb-0">
                            <i class="fas fa-map-marked-alt mr-1"></i> Titik Geofencing Absensi
                            @if(!empty($googleMapsKey))
                                <span class="badge badge-success ml-1"><i class="fab fa-google mr-1"></i> Google Maps Platform</span>
                            @else
                                <span class="badge badge-secondary ml-1">OpenStreetMap</span>
                            @endif
                        </h6>
                        <button type="button" class="btn btn-outline-primary btn-sm" id="btnGetLocation">
                            <i class="fas fa-crosshairs mr-1"></i> Gunakan Lokasi Saya Saat Ini
                        </button>
                    </div>

                    @if(empty($googleMapsKey))
                        <div class="alert alert-info py-2 px-3 small mb-2">
                            <i class="fas fa-info-circle mr-1"></i>
                            <strong>Info Google Maps:</strong> Untuk mengaktifkan pencarian tempat Google resmi (Google Places), masukkan <code>GOOGLE_MAPS_API_KEY</code> di file <code>.env</code> Anda (Google menyediakan kuota gratis $200 setiap bulan).
                        </div>
                    @endif

                    <!-- Tempel Link Google Maps / Koordinat -->
                    <div class="card bg-light border p-2 mb-2">
                        <label for="googleMapsUrlInput" class="small font-weight-bold text-dark mb-1">
                            <i class="fab fa-google text-danger mr-1"></i> Tempel Link Google Maps atau Koordinat (Cepat & Akurat)
                        </label>
                        <div class="input-group">
                            <input type="text" class="form-control form-control-sm" id="googleMapsUrlInput" placeholder="Contoh: https://maps.app.goo.gl/... atau koordinat -6.2088, 106.8456">
                            <div class="input-group-append">
                                <button class="btn btn-danger btn-sm" type="button" id="btnApplyGoogleMapsUrl">
                                    <i class="fas fa-map-pin mr-1"></i> Terapkan Titik
                                </button>
                            </div>
                        </div>
                        <small class="form-text text-muted mt-1" style="font-size: 11px;">
                            <i class="fas fa-info-circle mr-1 text-primary"></i> Cari lokasi di Google Maps (HP/Laptop) &rarr; klik <strong>Bagikan / Salin Tautan</strong> (atau salin koordinat) &rarr; tempel di sini &rarr; klik <strong>Terapkan Titik</strong>.
                        </small>
                    </div>

                    <!-- Kotak Pencarian Tempat -->
                    <div class="form-group mb-2 position-relative">
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text bg-white"><i class="fas fa-search text-muted"></i></span>
                            </div>
                            <input type="text" class="form-control" id="searchLocationInput" placeholder="Cari nama tempat, masjid, kelurahan, atau jalan di Google Maps...">
                            @if(empty($googleMapsKey))
                                <div class="input-group-append">
                                    <button class="btn btn-primary" type="button" id="btnSearchLocation">
                                        <i class="fas fa-search mr-1"></i> Cari Lokasi
                                    </button>
                                </div>
                            @endif
                        </div>
                        <!-- Dropdown Hasil Pencarian (untuk fallback non-Google API) -->
                        <div id="searchResultsList" class="list-group position-absolute w-100 shadow" style="display: none;"></div>
                    </div>

                    <!-- Wadah Peta -->
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
                            <small class="form-text text-muted">Toleransi radius absensi (default: 100m).</small>
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
@if(!empty($googleMapsKey))
    <!-- Google Maps Platform Resmi (Maps JavaScript API + Places Autocomplete) -->
    <script src="https://maps.googleapis.com/maps/api/js?key={{ $googleMapsKey }}&libraries=places&callback=initGoogleMap" async defer></script>
@else
    <!-- Fallback OpenStreetMap Leaflet jika API Key belum dipasang -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
@endif

<script>
    const isGoogleMaps = {{ !empty($googleMapsKey) ? 'true' : 'false' }};
    const defaultLat = -6.1753924;
    const defaultLng = 106.8271528;
    const defaultRadius = 100;

    let gMap = null;
    let gMarker = null;
    let gCircle = null;
    let gGeocoder = null;

    let lMap = null;
    let lMarker = null;
    let lCircle = null;
    let reverseGeocodeTimer = null;

    $(document).ready(function () {
        $('.loading').hide();
        $('.error-nama').hide();
        $('#alamatLoading').hide();
        $('#searchResultsList').hide();

        if (!isGoogleMaps) {
            initLeafletMap();
        }

        // Close search results on click outside
        $(document).on('click', function (e) {
            if (!$(e.target).closest('#searchLocationInput, #btnSearchLocation, #searchResultsList').length) {
                $('#searchResultsList').hide();
            }
        });
    });

    /* =======================================================================
       IMPLEMENTASI GOOGLE MAPS PLATFORM RESMI (Places Autocomplete + Geocoder)
       ======================================================================= */
    window.initGoogleMap = function () {
        const centerPos = { lat: defaultLat, lng: defaultLng };

        gMap = new google.maps.Map(document.getElementById('mapLokasi'), {
            center: centerPos,
            zoom: 16,
            mapTypeId: 'roadmap',
            mapTypeControl: true,
            mapTypeControlOptions: {
                style: google.maps.MapTypeControlStyle.DROPDOWN_MENU,
                position: google.maps.ControlPosition.TOP_RIGHT
            },
            streetViewControl: false,
            fullscreenControl: true
        });

        gGeocoder = new google.maps.Geocoder();

        // Marker Google Maps (Draggable)
        gMarker = new google.maps.Marker({
            position: centerPos,
            map: gMap,
            draggable: true,
            title: 'Geser untuk menentukan titik lokasi absensi',
            animation: google.maps.Animation.DROP
        });

        // Lingkaran Radius Geofencing Google Maps
        gCircle = new google.maps.Circle({
            map: gMap,
            radius: defaultRadius,
            fillColor: '#007bff',
            fillOpacity: 0.18,
            strokeColor: '#007bff',
            strokeWeight: 2
        });
        gCircle.bindTo('center', gMarker, 'position');

        // Google Places Autocomplete Search Box (Pencarian Resmi Google)
        const searchInput = document.getElementById('searchLocationInput');
        const autocomplete = new google.maps.places.Autocomplete(searchInput, {
            componentRestrictions: { country: 'id' },
            fields: ['geometry', 'name', 'formatted_address']
        });
        autocomplete.bindTo('bounds', gMap);

        autocomplete.addListener('place_changed', function () {
            const place = autocomplete.getPlace();
            if (!place.geometry || !place.geometry.location) {
                toastMixin.fire({
                    icon: 'warning',
                    title: 'Tempat tidak ditemukan di Google Maps.'
                });
                return;
            }

            gMap.setCenter(place.geometry.location);
            gMap.setZoom(17);
            gMarker.setPosition(place.geometry.location);

            updateFormCoordinates(place.geometry.location.lat(), place.geometry.location.lng());

            if (place.formatted_address) {
                $('#alamat').val(place.formatted_address);
            }

            toastMixin.fire({
                icon: 'success',
                title: 'Titik lokasi Google Maps berhasil ditentukan!'
            });
        });

        // Event: Marker dragged
        gMarker.addListener('dragend', function (e) {
            updateFormCoordinates(e.latLng.lat(), e.latLng.lng());
            reverseGeocodeGoogle(e.latLng.lat(), e.latLng.lng());
        });

        // Event: Map clicked
        gMap.addListener('click', function (e) {
            gMarker.setPosition(e.latLng);
            updateFormCoordinates(e.latLng.lat(), e.latLng.lng());
            reverseGeocodeGoogle(e.latLng.lat(), e.latLng.lng());
        });
    };

    function reverseGeocodeGoogle(lat, lng) {
        if (!gGeocoder) return;

        $('#alamatLoading').show();
        gGeocoder.geocode({ location: { lat: lat, lng: lng } }, function (results, status) {
            $('#alamatLoading').hide();
            if (status === 'OK' && results[0]) {
                $('#alamat').val(results[0].formatted_address);
            }
        });
    }

    function setGoogleMapPosition(lat, lng, radius) {
        if (!lat || !lng || isNaN(lat) || isNaN(lng)) {
            lat = defaultLat;
            lng = defaultLng;
        }
        if (!radius || isNaN(radius)) {
            radius = defaultRadius;
        }

        const latLng = new google.maps.LatLng(lat, lng);
        if (gMap && gMarker && gCircle) {
            gMarker.setPosition(latLng);
            gCircle.setRadius(radius);
            gMap.setCenter(latLng);
            gMap.setZoom(16);
        }
    }

    /* =======================================================================
       IMPLEMENTASI FALLBACK OPENSTREETMAP LEAFLET (Jika Google API Key Kosong)
       ======================================================================= */
    function initLeafletMap() {
        if (lMap !== null) return;

        lMap = L.map('mapLokasi').setView([defaultLat, defaultLng], 15);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
        }).addTo(lMap);

        lMarker = L.marker([defaultLat, defaultLng], { draggable: true }).addTo(lMap);

        lCircle = L.circle([defaultLat, defaultLng], {
            radius: defaultRadius,
            color: '#007bff',
            fillColor: '#007bff',
            fillOpacity: 0.18,
            weight: 2
        }).addTo(lMap);

        lMarker.on('dragend', function () {
            const pos = lMarker.getLatLng();
            updateFormCoordinates(pos.lat, pos.lng);
            lCircle.setLatLng(pos);
            reverseGeocodeOsm(pos.lat, pos.lng);
        });

        lMap.on('click', function (e) {
            lMarker.setLatLng(e.latlng);
            lCircle.setLatLng(e.latlng);
            updateFormCoordinates(e.latlng.lat, e.latlng.lng);
            reverseGeocodeOsm(e.latlng.lat, e.latlng.lng);
        });
    }

    function reverseGeocodeOsm(lat, lng) {
        $('#alamatLoading').show();
        clearTimeout(reverseGeocodeTimer);
        reverseGeocodeTimer = setTimeout(function () {
            fetch('https://nominatim.openstreetmap.org/reverse?format=json&lat=' + lat + '&lon=' + lng, {
                headers: { 'Accept': 'application/json' }
            })
            .then(res => res.json())
            .then(data => {
                $('#alamatLoading').hide();
                if (data && data.display_name) {
                    $('#alamat').val(data.display_name);
                }
            })
            .catch(() => {
                $('#alamatLoading').hide();
            });
        }, 300);
    }

    function setLeafletMapPosition(lat, lng, radius) {
        if (!lat || !lng || isNaN(lat) || isNaN(lng)) {
            lat = defaultLat;
            lng = defaultLng;
        }
        if (!radius || isNaN(radius)) {
            radius = defaultRadius;
        }

        const latLng = L.latLng(lat, lng);
        if (lMarker && lCircle && lMap) {
            lMarker.setLatLng(latLng);
            lCircle.setLatLng(latLng);
            lCircle.setRadius(radius);
            lMap.setView(latLng, 16);
        }
    }

    /* =======================================================================
       HELPER KOORDINAT & EVENT HANDLER BERSAMA
       ======================================================================= */
    function updateFormCoordinates(lat, lng) {
        $('#latitude').val(lat.toFixed(8));
        $('#longitude').val(lng.toFixed(8));
    }

    function setMapPosition(lat, lng, radius) {
        if (isGoogleMaps) {
            setGoogleMapPosition(lat, lng, radius);
        } else {
            setLeafletMapPosition(lat, lng, radius);
        }
    }

    // Modal shown event -> resize peta agar tidak abu-abu
    $('#modalInput').on('shown.bs.modal', function () {
        setTimeout(function () {
            const lat = parseFloat($('#latitude').val()) || defaultLat;
            const lng = parseFloat($('#longitude').val()) || defaultLng;
            const rad = parseInt($('#radius').val()) || defaultRadius;

            if (isGoogleMaps && gMap) {
                google.maps.event.trigger(gMap, 'resize');
                setGoogleMapPosition(lat, lng, rad);
            } else if (!isGoogleMaps && lMap) {
                lMap.invalidateSize();
                setLeafletMapPosition(lat, lng, rad);
            }
        }, 250);
    });

    // Update radius lingkaran ketika input radius berubah
    $('#radius').on('input change', function () {
        const rad = parseInt($(this).val()) || defaultRadius;
        if (isGoogleMaps && gCircle) {
            gCircle.setRadius(rad);
        } else if (!isGoogleMaps && lCircle) {
            lCircle.setRadius(rad);
        }
    });

    // Update posisi saat koordinat diketik manual
    $('#latitude, #longitude').on('change', function () {
        const lat = parseFloat($('#latitude').val());
        const lng = parseFloat($('#longitude').val());
        const rad = parseInt($('#radius').val()) || defaultRadius;

        if (!isNaN(lat) && !isNaN(lng)) {
            setMapPosition(lat, lng, rad);
            if (isGoogleMaps) {
                reverseGeocodeGoogle(lat, lng);
            } else {
                reverseGeocodeOsm(lat, lng);
            }
        }
    });

    // Tombol Gunakan Lokasi Saya Saat Ini (GPS)
    $('#btnGetLocation').on('click', function () {
        if (!navigator.geolocation) {
            toastMixin.fire({
                icon: 'error',
                title: 'Perangkat Anda tidak mendukung fitur Geolocation.'
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

                if (isGoogleMaps) {
                    reverseGeocodeGoogle(lat, lng);
                } else {
                    reverseGeocodeOsm(lat, lng);
                }

                $btn.prop('disabled', false).html('<i class="fas fa-crosshairs mr-1"></i> Gunakan Lokasi Saya Saat Ini');
                toastMixin.fire({
                    icon: 'success',
                    title: 'Lokasi Anda berhasil dideteksi dan disinkronkan!'
                });
            },
            function (error) {
                $btn.prop('disabled', false).html('<i class="fas fa-crosshairs mr-1"></i> Gunakan Lokasi Saya Saat Ini');
                let message = 'Gagal mendeteksi lokasi.';
                if (error.code === error.PERMISSION_DENIED) {
                    message = 'Izin akses lokasi ditolak oleh browser/perangkat.';
                }
                toastMixin.fire({
                    icon: 'error',
                    title: message
                });
            },
            { enableHighAccuracy: true, timeout: 10000, maximumAge: 0 }
        );
    });

    // =========================================================================
    // PARSING & PENERAPAN LINK GOOGLE MAPS / KOORDINAT
    // =========================================================================
    $('#btnApplyGoogleMapsUrl').on('click', function () {
        applyGoogleMapsUrl();
    });

    $('#googleMapsUrlInput').on('keypress', function (e) {
        if (e.which === 13) {
            e.preventDefault();
            applyGoogleMapsUrl();
        }
    });

    function applyGoogleMapsUrl() {
        const input = $('#googleMapsUrlInput').val().trim();
        if (!input) {
            toastMixin.fire({
                icon: 'warning',
                title: 'Silakan tempelkan link Google Maps atau angka koordinat terlebih dahulu.'
            });
            return;
        }

        // 1. Cek langsung format koordinat mentah e.g. -6.175392, 106.827153
        const rawCoordMatch = input.match(/^(-?\d{1,2}(?:\.\d+)?)\s*,\s*(-?\d{1,3}(?:\.\d+)?)$/);
        if (rawCoordMatch) {
            applyExtractedCoordinates(parseFloat(rawCoordMatch[1]), parseFloat(rawCoordMatch[2]));
            return;
        }

        // 2. Cek langsung link yang sudah memiliki @lat,lng
        const atMatch = input.match(/@(-?\d+\.\d+),(-?\d+\.\d+)/);
        if (atMatch) {
            applyExtractedCoordinates(parseFloat(atMatch[1]), parseFloat(atMatch[2]));
            return;
        }

        // 3. Jika berupa short link (maps.app.goo.gl / goo.gl/maps) -> panggil backend resolver
        const $btn = $('#btnApplyGoogleMapsUrl');
        $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i> Memproses...');

        $.ajax({
            type: "POST",
            url: "{{ route('kelompok.master_data.lokasi.parse_maps_url') }}",
            data: {
                _token: "{{ csrf_token() }}",
                url: input
            },
            success: function (res) {
                $btn.prop('disabled', false).html('<i class="fas fa-map-pin mr-1"></i> Terapkan Titik');
                if (res.status === 'success') {
                    applyExtractedCoordinates(res.latitude, res.longitude);
                } else {
                    toastMixin.fire({
                        icon: 'error',
                        title: res.message || 'Gagal mengekstrak koordinat dari link tersebut.'
                    });
                }
            },
            error: function (xhr) {
                $btn.prop('disabled', false).html('<i class="fas fa-map-pin mr-1"></i> Terapkan Titik');
                const msg = xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : 'Link Google Maps tidak valid atau titik tidak ditemukan.';
                toastMixin.fire({
                    icon: 'error',
                    title: msg
                });
            }
        });
    }

    function applyExtractedCoordinates(lat, lng) {
        const rad = parseInt($('#radius').val()) || defaultRadius;
        updateFormCoordinates(lat, lng);
        setMapPosition(lat, lng, rad);

        if (isGoogleMaps) {
            reverseGeocodeGoogle(lat, lng);
        } else {
            reverseGeocodeOsm(lat, lng);
        }

        toastMixin.fire({
            icon: 'success',
            title: 'Titik lokasi Google Maps berhasil diterapkan ke peta!'
        });
    }

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
        $('#kelompok_id').val('');
        $('#alamat').val('');
        $('#googleMapsUrlInput').val('');
        $('#searchLocationInput').val('');
        $('#searchResultsList').hide().empty();
        $('#latitude').val('');
        $('#longitude').val('');
        $('#radius').val('100');
        $('#status').val('1');

        $('.error-nama').hide();
        $('#alamatLoading').hide();

        setMapPosition(defaultLat, defaultLng, defaultRadius);
    });

    $(document).on("click", ".update-data", function () {
        $('.loading').hide();

        $('#btnSave').html('<i class="fas fa-check mr-1"></i> Perbarui');
        $('#title').text('Ubah');

        const id = $(this).data('id');
        const nama = $(this).data('nama');
        const kelompokId = $(this).data('kelompok-id');
        const alamat = $(this).data('alamat');
        const latitude = $(this).data('latitude');
        const longitude = $(this).data('longitude');
        const radius = $(this).data('radius') || 100;
        const status = $(this).data('status');

        $('#id').val(id);
        $('#nama').val(nama);
        $('#kelompok_id').val(kelompokId || '');
        $('#alamat').val(alamat);
        $('#googleMapsUrlInput').val('');
        $('#searchLocationInput').val('');
        $('#searchResultsList').hide().empty();
        $('#latitude').val(latitude || '');
        $('#longitude').val(longitude || '');
        $('#radius').val(radius);
        $('#status').val(status);

        $('.error-nama').hide();
        $('#alamatLoading').hide();

        const latVal = parseFloat(latitude) || defaultLat;
        const lngVal = parseFloat(longitude) || defaultLng;
        setMapPosition(latVal, lngVal, parseInt(radius));
    });

    $(document).on('click', '.toggle-status', function (e) {
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
                    data: { _token: "{{ csrf_token() }}" },
                    success: function (data) {
                        if (data.status === "success") {
                            toastMixin.fire({
                                icon: 'success',
                                title: data.message || ('Status lokasi ' + nama + ' berhasil diperbarui.')
                            });
                            location.reload();
                        } else {
                            toastMixin.fire({
                                icon: 'error',
                                title: data.message || ('Gagal mengubah status lokasi ' + nama)
                            });
                        }
                    },
                    error: function () {
                        toastMixin.fire({
                            icon: 'error',
                            title: 'Terjadi kesalahan pada sistem.'
                        });
                    }
                });
            }
        });
    });
</script>
@endsection
