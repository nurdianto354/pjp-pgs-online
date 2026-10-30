@extends('layouts.app')

@section('content')
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">Detail Rekap Presensi</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item">Data Kelompok</li>
                    <li class="breadcrumb-item"><a href="{{ route('kelompok.absensi.index') }}">Absensi Kegiatan</a></li>
                    <li class="breadcrumb-item active">Detail Rekap</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">
        <!-- Informasi Header Jadwal -->
        <div class="card card-outline card-success shadow-sm mb-3">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center flex-wrap">
                    <div>
                        <h4 class="font-weight-bold text-dark mb-1">
                            {{ $jadwal->getKegiatan ? $jadwal->getKegiatan->nama : 'Jadwal Kegiatan' }}
                        </h4>
                        <div class="text-muted small">
                            <span class="mr-3">
                                <i class="far fa-calendar-alt text-primary mr-1"></i>
                                {{ $jadwal->tanggal ? $jadwal->tanggal->format('d M Y') : '-' }}
                            </span>
                            <span class="mr-3">
                                <i class="far fa-clock text-info mr-1"></i>
                                {{ substr($jadwal->jam_mulai, 0, 5) }} - {{ substr($jadwal->jam_selesai, 0, 5) }} WIB
                            </span>
                            <span class="mr-3">
                                <i class="fas fa-tag text-warning mr-1"></i>
                                Tipe: <strong>{{ strtoupper($jadwal->tipe) }}</strong>
                            </span>
                        </div>
                    </div>
                    <div class="mt-2 mt-md-0">
                        <a href="{{ route('kelompok.absensi.public_rekap', $jadwal->id) }}" target="_blank" class="btn btn-info btn-sm shadow-sm mr-1">
                            <i class="fas fa-list-alt mr-1"></i> Rekap Publik
                        </a>
                        <a href="{{ route('kelompok.absensi.public_form', $jadwal->id) }}" target="_blank" class="btn btn-success btn-sm shadow-sm mr-1">
                            <i class="fas fa-external-link-alt mr-1"></i> Buka Form Publik
                        </a>
                        <a href="{{ route('kelompok.absensi.index') }}" class="btn btn-secondary btn-sm shadow-sm">
                            <i class="fas fa-arrow-left mr-1"></i> Kembali
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Kartu Ringkasan Status Kehadiran (Hadir, SDC, Izin) -->
        <div class="row">
            <div class="col-md-4 col-sm-6 col-12">
                <div class="info-box shadow-sm">
                    <span class="info-box-icon bg-success"><i class="fas fa-user-check"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Hadir (In-Radius)</span>
                        <span class="info-box-number text-success h4 font-weight-bold">{{ $countHadir }}</span>
                    </div>
                </div>
            </div>
            <div class="col-md-4 col-sm-6 col-12">
                <div class="info-box shadow-sm">
                    <span class="info-box-icon bg-primary"><i class="fas fa-briefcase"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">SDC (Sambung Dinas/Tugas)</span>
                        <span class="info-box-number text-primary h4 font-weight-bold">{{ $countSDC }}</span>
                    </div>
                </div>
            </div>
            <div class="col-md-4 col-sm-6 col-12">
                <div class="info-box shadow-sm">
                    <span class="info-box-icon bg-warning"><i class="fas fa-envelope-open-text text-white"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Izin</span>
                        <span class="info-box-number text-warning h4 font-weight-bold">{{ $countIzin }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabel Detail Kehadiran Jamaah -->
        <div class="card card-outline card-success shadow-sm">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-list-ul mr-1"></i>
                    Daftar Jamaah yang Telah Melakukan Presensi ({{ $presensis->count() }} Orang)
                </h3>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table id="dataTables" class="table table-bordered table-striped table-hover w-100">
                        <thead>
                            <tr class="text-center align-middle">
                                <th style="width: 5%;">No</th>
                                <th style="width: 25%;">Nama Jamaah</th>
                                <th style="width: 15%;">Divisi</th>
                                <th style="width: 14%;">Status</th>
                                <th style="width: 26%;">Detail Kehadiran / Keterangan</th>
                                <th style="width: 15%;">Waktu Presensi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($presensis as $key => $p)
                                @php
                                    $jamaah = $p->getJamaah;
                                    $divisiNama = $jamaah && $jamaah->getDivisi ? $jamaah->getDivisi->nama : 'Kelompok';
                                @endphp
                                <tr>
                                    <td class="text-center align-middle">{{ ++$key }}</td>
                                    <td class="align-middle">
                                        <div class="font-weight-bold text-dark">
                                            {{ $jamaah ? $jamaah->nama_lengkap : 'Jamaah ID: ' . $p->jamaah_id }}
                                        </div>
                                        <small class="text-muted">
                                            {{ $jamaah && $jamaah->jenis_kelamin == 1 ? 'Laki-laki' : 'Perempuan' }}
                                        </small>
                                    </td>
                                    <td class="text-center align-middle">
                                        @if($jamaah && !empty($jamaah->getDivisi))
                                            <span class="badge badge-info px-2 py-1">{{ $divisiNama }}</span>
                                        @else
                                            <span class="badge badge-success px-2 py-1" title="Divisi Kelompok">
                                                <i class="fas fa-users mr-1"></i> Kelompok
                                            </span>
                                        @endif
                                    </td>
                                    <td class="text-center align-middle">
                                        @if($p->status == 'hadir')
                                            <span class="badge badge-success px-2 py-1 shadow-sm">
                                                <i class="fas fa-check-circle mr-1"></i> HADIR
                                            </span>
                                        @elseif($p->status == 'sdc')
                                            <span class="badge badge-primary px-2 py-1 shadow-sm">
                                                <i class="fas fa-briefcase mr-1"></i> SDC
                                            </span>
                                        @elseif($p->status == 'izin')
                                            <span class="badge badge-warning px-2 py-1 text-dark shadow-sm">
                                                <i class="fas fa-envelope-open-text mr-1"></i> IZIN
                                            </span>
                                        @endif
                                    </td>
                                    <td class="align-middle small">
                                        @if($p->status == 'hadir')
                                            @if($p->getLokasi)
                                                <div class="text-success font-weight-bold">
                                                    <i class="fas fa-map-marker-alt mr-1"></i> {{ $p->getLokasi->nama }}
                                                </div>
                                                <div class="text-muted">Jarak: {{ $p->jarak_meter ?? 0 }}m (Valid Radius)</div>
                                            @else
                                                <span class="text-success"><i class="fas fa-check mr-1"></i> Hadir Tepat Waktu</span>
                                            @endif
                                        @else
                                            <div class="font-italic text-dark">
                                                <i class="fas fa-comment-alt mr-1 text-secondary"></i>
                                                "{{ $p->keterangan ?: '-' }}"
                                            </div>
                                        @endif
                                    </td>
                                    <td class="text-center align-middle small text-muted">
                                        {{ $p->waktu_presensi ? $p->waktu_presensi->format('d M Y H:i') : '-' }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center text-muted py-4">
                                        <i class="fas fa-clipboard-list fa-2x mb-2 d-block text-secondary"></i>
                                        Belum ada jamaah yang melakukan presensi pada jadwal ini.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
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
                    "search": "Cari Presensi:",
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
</script>
@endsection

