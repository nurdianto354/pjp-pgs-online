@extends('layouts.app')

@section('content')
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">Absensi Kegiatan Kelompok</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item">Data Kelompok</li>
                    <li class="breadcrumb-item active"><a href="{{ route('kelompok.absensi.index') }}">Absensi Kegiatan</a></li>
                </ol>
            </div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card card-outline card-success shadow-sm">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-clipboard-check mr-1"></i>
                            Monitoring & Rekapitulasi Presensi Sesi Kegiatan
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-light border mb-3 small text-muted">
                            <i class="fas fa-info-circle text-info mr-1"></i>
                            Gunakan tombol <strong>Salin Link</strong> pada jadwal terkait untuk membagikan tautan presensi ke grup WhatsApp jamaah atau buka langsung tanpa login.
                        </div>

                        <div class="table-responsive">
                            <table id="dataTables" class="table table-bordered table-striped table-hover w-100">
                                <thead>
                                    <tr class="text-center align-middle">
                                        <th style="width: 5%;">No</th>
                                        <th style="width: 22%;">Kegiatan</th>
                                        <th style="width: 10%;">Tipe</th>
                                        <th style="width: 16%;">Waktu Pelaksanaan</th>
                                        <th style="width: 22%;">Statistik Kehadiran</th>
                                        <th style="width: 10%;">Status Sesi</th>
                                        <th style="width: 15%;">Aksi Presensi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($jadwals as $key => $jadwal)
                                        @php
                                            $statJadwal = $stats[$jadwal->id] ?? collect();
                                            $hadirCount = $statJadwal->where('status', 'hadir')->first()->total ?? 0;
                                            $sdcCount   = $statJadwal->where('status', 'sdc')->first()->total ?? 0;
                                            $izinCount  = $statJadwal->where('status', 'izin')->first()->total ?? 0;
                                            $totalPresensi = $hadirCount + $sdcCount + $izinCount;
                                            $publicUrl = route('kelompok.absensi.public_form', $jadwal->id);
                                        @endphp
                                        <tr>
                                            <td class="text-center align-middle">{{ ++$key }}</td>
                                            <td class="align-middle">
                                                <div class="font-weight-bold text-dark">
                                                    {{ $jadwal->getKegiatan ? $jadwal->getKegiatan->nama : 'Kegiatan' }}
                                                </div>
                                                <div class="small text-muted mt-1">
                                                    <i class="fas fa-map-marker-alt text-danger mr-1"></i>
                                                    {{ $jadwal->getLokasi->count() }} Lokasi Geofencing
                                                </div>
                                            </td>
                                            <td class="text-center align-middle">
                                                @if($jadwal->tipe == 'turba')
                                                    <span class="badge badge-primary px-2 py-1"><i class="fas fa-route mr-1"></i> TURBA</span>
                                                @else
                                                    <span class="badge badge-warning px-2 py-1 text-dark"><i class="fas fa-bullseye mr-1"></i> TOTAL</span>
                                                @endif
                                            </td>
                                            <td class="align-middle small">
                                                <div>
                                                    <i class="far fa-calendar-alt text-primary mr-1"></i>
                                                    <strong>{{ $jadwal->tanggal ? $jadwal->tanggal->format('d M Y') : '-' }}</strong>
                                                </div>
                                                <div class="text-muted mt-1">
                                                    <i class="far fa-clock text-info mr-1"></i>
                                                    {{ substr($jadwal->jam_mulai, 0, 5) }} - {{ substr($jadwal->jam_selesai, 0, 5) }} WIB
                                                </div>
                                            </td>
                                            <td class="align-middle">
                                                <div class="d-flex align-items-center justify-content-between mb-1">
                                                    <span class="badge badge-success px-2 py-1 mr-1" title="Hadir (Lolos Geofencing)">
                                                        Hadir: {{ $hadirCount }}
                                                    </span>
                                                    <span class="badge badge-primary px-2 py-1 mr-1" title="SDC (Sambung Dinas/Tugas)">
                                                        SDC: {{ $sdcCount }}
                                                    </span>
                                                    <span class="badge badge-warning px-2 py-1 text-dark mr-1" title="Izin">
                                                        Izin: {{ $izinCount }}
                                                    </span>
                                                </div>
                                                <div class="small text-muted font-weight-bold">
                                                    Total Tercatat: {{ $totalPresensi }} Orang
                                                </div>
                                            </td>
                                            <td class="text-center align-middle">
                                                @if($jadwal->status)
                                                    <span class="badge badge-success px-2 py-1"><i class="fas fa-check-circle mr-1"></i> Dibuka</span>
                                                @else
                                                    <span class="badge badge-secondary px-2 py-1"><i class="fas fa-lock mr-1"></i> Ditutup</span>
                                                @endif
                                            </td>
                                            <td class="text-center align-middle">
                                                <div class="btn-group-vertical btn-group-sm w-100" role="group">
                                                    <a href="{{ route('kelompok.absensi.detail', $jadwal->id) }}" class="btn btn-info btn-sm mb-1" title="Lihat Rekap Kehadiran">
                                                        <i class="fas fa-list-alt mr-1"></i> Rekap Presensi
                                                    </a>
                                                    <div class="btn-group btn-group-sm" role="group">
                                                        <a href="{{ $publicUrl }}" target="_blank" class="btn btn-outline-success" title="Buka Form Presensi Publik">
                                                            <i class="fas fa-external-link-alt mr-1"></i> Buka
                                                        </a>
                                                        <button type="button" class="btn btn-outline-secondary btn-copy-link" data-url="{{ $publicUrl }}" title="Salin Tautan Presensi">
                                                            <i class="fas fa-copy mr-1"></i> Salin
                                                        </button>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="7" class="text-center text-muted py-4">
                                                <i class="fas fa-calendar-times fa-2x mb-2 d-block text-secondary"></i>
                                                Belum ada jadwal kegiatan yang dibuat. Silakan tambahkan jadwal kegiatan terlebih dahulu.
                                            </td>
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
@endsection

@section('js')
<script>
    $(document).ready(function () {
        if (!$.fn.DataTable.isDataTable('#dataTables')) {
            $('#dataTables').DataTable({
                "paging": true,
                "lengthChange": true,
                "searching": true,
                "ordering": true,
                "info": true,
                "autoWidth": false,
                "responsive": true,
                "language": {
                    "emptyTable": "Tidak ada data yang tersedia pada tabel",
                    "info": "Menampilkan _START_ sampai _END_ dari _TOTAL_ data",
                    "infoEmpty": "Menampilkan 0 sampai 0 dari 0 data",
                    "infoFiltered": "(disaring dari _MAX_ total entri)",
                    "lengthMenu": "Tampilkan _MENU_ data per halaman",
                    "loadingRecords": "Memuat...",
                    "processing": "Sedang memproses...",
                    "search": "Cari Jadwal:",
                    "zeroRecords": "Tidak ditemukan data yang sesuai",
                    "paginate": {
                        "first": "Pertama",
                        "last": "Terakhir",
                        "next": "Berikutnya",
                        "previous": "Sebelumnya"
                    }
                }
            });
        }
    });

    // Copy URL Link Presensi ke Clipboard
    $(document).on('click', '.btn-copy-link', function () {
        const url = $(this).data('url');
        if (navigator.clipboard && navigator.clipboard.writeText) {
            navigator.clipboard.writeText(url).then(function () {
                Swal.fire({
                    toast: true,
                    position: 'top-end',
                    icon: 'success',
                    title: 'Link presensi berhasil disalin!',
                    showConfirmButton: false,
                    timer: 2500
                });
            });
        } else {
            // Fallback copy textarea
            const tempInput = document.createElement("input");
            tempInput.value = url;
            document.body.appendChild(tempInput);
            tempInput.select();
            document.execCommand("copy");
            document.body.removeChild(tempInput);

            Swal.fire({
                toast: true,
                position: 'top-end',
                icon: 'success',
                title: 'Link presensi berhasil disalin!',
                showConfirmButton: false,
                timer: 2500
            });
        }
    });
</script>
@endsection

