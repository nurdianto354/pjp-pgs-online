@extends('layouts.app')

@section('content')
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">Jadwal Kegiatan Kelompok</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item">Data Kelompok</li>
                    <li class="breadcrumb-item active"><a href="{{ route('kelompok.jadwal.index') }}">Jadwal Kegiatan</a></li>
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
                            <i class="fas fa-calendar-check mr-1"></i>
                            Daftar Jadwal & Sesi Kegiatan Kelompok
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="mb-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
                            <button type="button" class="btn btn-success btn-sm shadow-sm" data-toggle="modal" data-target="#modalInput" id="tambahData">
                                <i class="fa fa-plus mr-1"></i> Tambah Jadwal Kegiatan
                            </button>
                            <span class="text-muted small">
                                <i class="fas fa-info-circle text-info mr-1"></i>
                                Tipe <b>Turba</b> mendukung multi-lokasi untuk geofencing presensi kegiatan keliling
                            </span>
                        </div>

                        <div class="table-responsive">
                            <table id="dataTables" class="table table-bordered table-striped table-hover w-100">
                                <thead>
                                    <tr class="text-center align-middle">
                                        <th style="width: 5%;">No</th>
                                        <th style="width: 20%;">Kegiatan</th>
                                        <th style="width: 12%;">Tipe</th>
                                        <th style="width: 24%;">Lokasi Pelaksanaan</th>
                                        <th style="width: 15%;">Waktu Pelaksanaan</th>
                                        <th style="width: 9%;">Status</th>
                                        <th style="width: 15%;">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($datas as $key => $data)
                                        @php
                                            $lokasiIds = $data->getLokasi->pluck('id')->toArray();
                                        @endphp
                                        <tr>
                                            <td class="text-center align-middle">{{ ++$key }}</td>
                                            <td class="align-middle">
                                                <div class="font-weight-bold text-dark">
                                                    {{ $data->getKegiatan ? $data->getKegiatan->nama : '-' }}
                                                </div>
                                                @if(!empty($data->keterangan))
                                                    <div class="small text-muted font-italic mt-1">
                                                        {{ Str::limit($data->keterangan, 60) }}
                                                    </div>
                                                @endif
                                            </td>
                                            <td class="text-center align-middle">
                                                @if($data->tipe == 'turba')
                                                    <span class="badge badge-primary px-2 py-1 shadow-sm">
                                                        <i class="fas fa-route mr-1"></i> TURBA
                                                    </span>
                                                    <div class="small text-muted mt-1">Multi-Lokasi</div>
                                                @else
                                                    <span class="badge badge-warning px-2 py-1 shadow-sm text-dark">
                                                        <i class="fas fa-bullseye mr-1"></i> TOTAL
                                                    </span>
                                                    <div class="small text-muted mt-1">Terpusat</div>
                                                @endif
                                            </td>
                                            <td class="align-middle">
                                                @if($data->getLokasi->isNotEmpty())
                                                    <div class="d-flex flex-wrap gap-1">
                                                        @foreach($data->getLokasi as $lokasi)
                                                            <span class="badge badge-light border px-2 py-1 mr-1 mb-1" title="Radius: {{ $lokasi->radius ?? 100 }}m">
                                                                <i class="fas fa-map-pin text-danger mr-1"></i> {{ $lokasi->nama }}
                                                            </span>
                                                        @endforeach
                                                    </div>
                                                @else
                                                    <span class="text-danger small font-italic">
                                                        <i class="fas fa-exclamation-triangle mr-1"></i> Belum ada lokasi
                                                    </span>
                                                @endif
                                            </td>
                                            <td class="align-middle small">
                                                <div>
                                                    <i class="far fa-calendar-alt text-primary mr-1"></i>
                                                    <strong>{{ $data->tanggal ? $data->tanggal->format('d M Y') : '-' }}</strong>
                                                </div>
                                                <div class="text-muted mt-1">
                                                    <i class="far fa-clock text-info mr-1"></i>
                                                    {{ substr($data->jam_mulai, 0, 5) }} - {{ substr($data->jam_selesai, 0, 5) }} WIB
                                                </div>
                                            </td>
                                            <td class="text-center align-middle">
                                                @if($data->status)
                                                    <span class="badge badge-success px-2 py-1"><i class="fas fa-check-circle mr-1"></i> Active</span>
                                                @else
                                                    <span class="badge badge-secondary px-2 py-1"><i class="fas fa-times-circle mr-1"></i> Non Active</span>
                                                @endif
                                            </td>
                                            <td class="text-center align-middle">
                                                <div class="btn-group btn-group-sm" role="group">
                                                    <a href="{{ route('kelompok.absensi.public_form', $data->id) }}" target="_blank" class="btn btn-success" title="Buka Form Presensi Publik (Tanpa Login)">
                                                        <i class="fas fa-clipboard-check"></i>
                                                    </a>
                                                    <button type="button" 
                                                            class="btn btn-warning edit-data" 
                                                            data-toggle="modal" 
                                                            data-target="#modalInput"
                                                            data-id="{{ $data->id }}"
                                                            data-kegiatan_id="{{ $data->kegiatan_id }}"
                                                            data-tipe="{{ $data->tipe }}"
                                                            data-lokasi_ids="{{ json_encode($lokasiIds) }}"
                                                            data-tanggal="{{ $data->tanggal ? $data->tanggal->format('Y-m-d') : '' }}"
                                                            data-jam_mulai="{{ substr($data->jam_mulai, 0, 5) }}"
                                                            data-jam_selesai="{{ substr($data->jam_selesai, 0, 5) }}"
                                                            data-keterangan="{{ $data->keterangan }}"
                                                            data-status="{{ $data->status ? 1 : 0 }}"
                                                            title="Ubah Jadwal">
                                                        <i class="fa fa-edit"></i>
                                                    </button>
                                                    <button type="button" 
                                                            class="btn {{ $data->status ? 'btn-outline-danger' : 'btn-outline-success' }} toggle-status"
                                                            data-id="{{ $data->id }}"
                                                            data-nama="{{ $data->getKegiatan ? $data->getKegiatan->nama : 'Jadwal' }}"
                                                            data-next-action="{{ $data->status ? 'menonaktifkan' : 'mengaktifkan' }}"
                                                            title="{{ $data->status ? 'Nonaktifkan Jadwal' : 'Aktifkan Jadwal' }}">
                                                        <i class="fa {{ $data->status ? 'fa-toggle-on text-success' : 'fa-toggle-off text-muted' }}"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="7" class="text-center text-muted py-4">
                                                <i class="fas fa-calendar-times fa-2x mb-2 d-block text-secondary"></i>
                                                Belum ada jadwal kegiatan. Klik tombol <strong>Tambah Jadwal Kegiatan</strong> untuk membuat agenda baru.
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

<!-- Modal Input / Edit -->
<div class="modal fade" data-keyboard="false" data-backdrop="static" id="modalInput" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header bg-light">
                <h5 class="modal-title font-weight-bold">
                    <span id="title">Tambah</span> Jadwal Kegiatan
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form name="input-data" method="POST" action="{{ route('kelompok.jadwal.create') }}" onsubmit="return validateForm()">
                @csrf
                <div class="modal-body">
                    <input type="hidden" id="id" name="id">

                    <div class="row">
                        <!-- Pilihan Kegiatan -->
                        <div class="col-md-5 form-group">
                            <label for="kegiatan_id">Pilih Kegiatan <span class="text-danger">*</span></label>
                            <select class="form-control select2-single" id="kegiatan_id" name="kegiatan_id" required style="width: 100%;">
                                <option value="">-- Pilih Master Kegiatan --</option>
                                @foreach($listKegiatan as $kegiatan)
                                    <option value="{{ $kegiatan->id }}">{{ $kegiatan->nama }}</option>
                                @endforeach
                            </select>
                            <small class="form-text text-danger error-kegiatan" style="display: none;">Harap pilih kegiatan!</small>
                        </div>

                        <!-- Pilihan Tipe -->
                        <div class="col-md-4 form-group">
                            <label for="tipe">Tipe Kegiatan <span class="text-danger">*</span></label>
                            <select class="form-control" id="tipe" name="tipe" required>
                                <option value="total">TOTAL (Terpusat)</option>
                                <option value="turba">TURBA (Turun ke Bawah / Keliling)</option>
                            </select>
                            <small class="form-text text-muted" id="tipeHint">
                                <i class="fas fa-info-circle text-info mr-1"></i> Tipe Total: terpusat pada satu lokasi utama.
                            </small>
                        </div>

                        <!-- Status -->
                        <div class="col-md-3 form-group">
                            <label for="status">Status Jadwal</label>
                            <select class="form-control" id="status" name="status">
                                <option value="1">Active (Aktif)</option>
                                <option value="0">Non Active (Nonaktif)</option>
                            </select>
                        </div>
                    </div>

                    <!-- Multi-Select Lokasi -->
                    <div class="form-group">
                        <label for="lokasi_ids" class="d-flex justify-content-between align-items-center">
                            <span>Pilih Lokasi Kegiatan (Multi-Lokasi) <span class="text-danger">*</span></span>
                            <small class="text-muted font-italic">Dapat memilih lebih dari 1 lokasi untuk geofencing</small>
                        </label>
                        <select class="form-control select2" id="lokasi_ids" name="lokasi_ids[]" multiple="multiple" style="width: 100%;" required>
                            @foreach($listLokasi as $lokasi)
                                <option value="{{ $lokasi->id }}">
                                    {{ $lokasi->nama }} (Radius: {{ $lokasi->radius ?? 100 }}m)
                                </option>
                            @endforeach
                        </select>
                        <small class="form-text text-danger error-lokasi" style="display: none;">Harap pilih minimal 1 lokasi pelaksanaan!</small>
                        <div class="alert alert-light border mt-2 py-1 px-2 small text-muted">
                            <i class="fas fa-shield-alt text-success mr-1"></i>
                            Peserta absensi dapat melakukan presensi di salah satu titik radius lokasi yang terdaftar di atas.
                        </div>
                    </div>

                    <div class="row">
                        <!-- Tanggal Pelaksanaan -->
                        <div class="col-md-4 form-group">
                            <label for="tanggal">Tanggal Pelaksanaan <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="tanggal" name="tanggal" required>
                            <small class="form-text text-danger error-tanggal" style="display: none;">Tanggal wajib diisi!</small>
                        </div>

                        <!-- Jam Mulai -->
                        <div class="col-md-4 form-group">
                            <label for="jam_mulai">Jam Mulai <span class="text-danger">*</span></label>
                            <input type="time" class="form-control" id="jam_mulai" name="jam_mulai" required>
                            <small class="form-text text-danger error-mulai" style="display: none;">Jam mulai wajib diisi!</small>
                        </div>

                        <!-- Jam Selesai -->
                        <div class="col-md-4 form-group">
                            <label for="jam_selesai">Jam Selesai <span class="text-danger">*</span></label>
                            <input type="time" class="form-control" id="jam_selesai" name="jam_selesai" required>
                            <small class="form-text text-danger error-selesai" style="display: none;">Jam selesai wajib diisi!</small>
                        </div>
                    </div>

                    <div class="form-group mb-0">
                        <label for="keterangan">Keterangan / Catatan Kegiatan</label>
                        <textarea class="form-control" id="keterangan" name="keterangan" rows="2" placeholder="Catatan opsional mengenai susunan acara, pemateri, dsb"></textarea>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <i class="fas fa-times mr-1"></i> Batal
                    </button>
                    <button type="submit" class="btn btn-success" id="btnSubmit">
                        <i class="fas fa-save mr-1"></i> Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
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

        // Inisialisasi Select2 Multi-Select untuk Lokasi
        if ($.fn.select2) {
            $('#lokasi_ids').select2({
                placeholder: "-- Pilih Satu atau Banyak Lokasi --",
                allowClear: true,
                closeOnSelect: false,
                dropdownParent: $('#modalInput')
            });
        }
    });

    // Perubahan tipe: berikan petunjuk interaktif
    $(document).on('change', '#tipe', function () {
        const val = $(this).val();
        if (val === 'turba') {
            $('#tipeHint').html('<i class="fas fa-route text-primary mr-1"></i> <b>Tipe Turba:</b> Kegiatan keliling, silakan pilih <b>banyak lokasi</b>.');
        } else {
            $('#tipeHint').html('<i class="fas fa-bullseye text-warning mr-1"></i> <b>Tipe Total:</b> Kegiatan serentak terpusat.');
        }
    });

    function validateForm() {
        let valid = true;
        const kegiatanId = $('#kegiatan_id').val();
        const lokasiIds  = $('#lokasi_ids').val();
        const tanggal    = $('#tanggal').val();
        const jamMulai   = $('#jam_mulai').val();
        const jamSelesai = $('#jam_selesai').val();

        if (!kegiatanId) {
            $('.error-kegiatan').show();
            valid = false;
        } else {
            $('.error-kegiatan').hide();
        }

        if (!lokasiIds || lokasiIds.length === 0) {
            $('.error-lokasi').show();
            valid = false;
        } else {
            $('.error-lokasi').hide();
        }

        if (!tanggal) {
            $('.error-tanggal').show();
            valid = false;
        } else {
            $('.error-tanggal').hide();
        }

        if (!jamMulai) {
            $('.error-mulai').show();
            valid = false;
        } else {
            $('.error-mulai').hide();
        }

        if (!jamSelesai) {
            $('.error-selesai').show();
            valid = false;
        } else {
            $('.error-selesai').hide();
        }

        return valid;
    }

    // Reset Modal Form
    $(document).on('click', '#tambahData', function () {
        $('#title').text('Tambah');
        $('#btnSubmit').html('<i class="fas fa-save mr-1"></i> Simpan');
        $('#id').val('');
        $('#kegiatan_id').val('').trigger('change');
        $('#tipe').val('total').trigger('change');
        $('#lokasi_ids').val([]).trigger('change');
        $('#tanggal').val(new Date().toISOString().slice(0, 10));
        $('#jam_mulai').val('19:30');
        $('#jam_selesai').val('21:30');
        $('#keterangan').val('');
        $('#status').val('1');

        $('.error-kegiatan').hide();
        $('.error-lokasi').hide();
        $('.error-tanggal').hide();
        $('.error-mulai').hide();
        $('.error-selesai').hide();
    });

    // Populate Modal Edit
    $(document).on('click', '.edit-data', function () {
        $('#title').text('Ubah');
        $('#btnSubmit').html('<i class="fas fa-save mr-1"></i> Perbarui');

        const id = $(this).data('id');
        const kegiatanId = $(this).data('kegiatan_id');
        const tipe = $(this).data('tipe');
        const lokasiIds = $(this).data('lokasi_ids');
        const tanggal = $(this).data('tanggal');
        const jamMulai = $(this).data('jam_mulai');
        const jamSelesai = $(this).data('jam_selesai');
        const keterangan = $(this).data('keterangan');
        const status = $(this).data('status');

        $('#id').val(id);
        $('#kegiatan_id').val(kegiatanId).trigger('change');
        $('#tipe').val(tipe).trigger('change');
        
        if (lokasiIds) {
            try {
                const parsed = typeof lokasiIds === 'string' ? JSON.parse(lokasiIds) : lokasiIds;
                $('#lokasi_ids').val(parsed).trigger('change');
            } catch (e) {
                $('#lokasi_ids').val([]).trigger('change');
            }
        } else {
            $('#lokasi_ids').val([]).trigger('change');
        }

        $('#tanggal').val(tanggal);
        $('#jam_mulai').val(jamMulai);
        $('#jam_selesai').val(jamSelesai);
        $('#keterangan').val(keterangan || '');
        $('#status').val(status);

        $('.error-kegiatan').hide();
        $('.error-lokasi').hide();
        $('.error-tanggal').hide();
        $('.error-mulai').hide();
        $('.error-selesai').hide();
    });

    // Toggle Status Ajax
    $(document).on('click', '.toggle-status', function (e) {
        e.preventDefault();

        const id = $(this).data('id');
        const nama = $(this).data('nama');
        const actionText = $(this).data('next-action');

        Swal.fire({
            title: "Ubah Status Jadwal?",
            text: "Apakah Anda yakin ingin " + actionText + " jadwal \"" + nama + "\"?",
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
                    url: "{{ url('/kelompok/jadwal/toggle-status') }}/" + id,
                    data: { _token: "{{ csrf_token() }}" },
                    success: function (data) {
                        if (data.status === "success") {
                            toastMixin.fire({
                                icon: 'success',
                                title: data.message || ('Status jadwal ' + nama + ' berhasil diperbarui.')
                            });
                            location.reload();
                        } else {
                            toastMixin.fire({
                                icon: 'error',
                                title: data.message || ('Gagal mengubah status jadwal ' + nama)
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

