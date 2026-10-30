@extends('layouts.app')

@section('content')
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">Data Personal / Jamaah</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item">Data Kelompok</li>
                    <li class="breadcrumb-item">Master Data</li>
                    <li class="breadcrumb-item active"><a href="{{ route('kelompok.master_data.jamaah.index') }}">Personal / Jamaah</a></li>
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
                            <i class="fas fa-user-friends mr-1"></i>
                            Master Data Personal / Jamaah Kelompok
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="mb-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
                            <div>
                                <button type="button" class="btn btn-success btn-sm shadow-sm mr-2" data-toggle="modal" data-target="#modalInput" id="tambahData">
                                    <i class="fa fa-plus mr-1"></i> Tambah Personal / Jamaah
                                </button>
                                <button type="button" class="btn btn-outline-primary btn-sm shadow-sm" id="btnSyncMurid">
                                    <i class="fas fa-sync-alt mr-1"></i> Sinkronisasi Data Murid
                                </button>
                            </div>
                            <span class="text-muted small">
                                <i class="fas fa-info-circle text-info mr-1"></i>
                                Otomatis menambah data baru & memperbarui data murid (Remaja & Pranikah)
                            </span>
                        </div>

                        <div class="table-responsive">
                            <table id="dataTables" class="table table-bordered table-striped table-hover w-100">
                                <thead>
                                    <tr class="text-center align-middle">
                                        <th style="width: 5%;">No</th>
                                        <th>Nama Lengkap</th>
                                        <th style="width: 15%;">Jenis Kelamin</th>
                                        <th style="width: 18%;">Divisi</th>
                                        <th style="width: 12%;">Status</th>
                                        <th style="width: 16%;">Diperbarui</th>
                                        <th style="width: 14%;">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($datas as $key => $data)
                                        <tr>
                                            <td class="text-center align-middle">{{ ++$key }}</td>
                                            <td class="align-middle">
                                                <div class="font-weight-bold text-dark">{{ $data->nama_lengkap }}</div>
                                                @if(!empty($data->getMurid))
                                                    <small class="text-muted">
                                                        <i class="fas fa-graduation-cap text-success mr-1"></i>
                                                        Data Murid: {{ $data->getMurid->nama_lengkap }}
                                                    </small>
                                                @endif
                                            </td>
                                            <td class="text-center align-middle">
                                                @if($data->jenis_kelamin == 1)
                                                    <span class="badge badge-info px-2 py-1">
                                                        <i class="fas fa-mars mr-1"></i> Laki-laki
                                                    </span>
                                                @else
                                                    <span class="badge badge-danger px-2 py-1" style="background-color: #e83e8c;">
                                                        <i class="fas fa-venus mr-1"></i> Perempuan
                                                    </span>
                                                @endif
                                            </td>
                                            <td class="text-center align-middle">
                                                @if(!empty($data->getDivisi))
                                                    @if(strtolower($data->getDivisi->nama) == 'remaja')
                                                        <span class="badge badge-primary px-2 py-1">
                                                            <i class="fas fa-user-tag mr-1"></i> Remaja
                                                        </span>
                                                    @elseif(strtolower($data->getDivisi->nama) == 'pranikah')
                                                        <span class="badge badge-purple px-2 py-1" style="background-color: #6f42c1; color: #fff;">
                                                            <i class="fas fa-heart mr-1"></i> Pranikah
                                                        </span>
                                                    @else
                                                        <span class="badge badge-secondary px-2 py-1">{{ $data->getDivisi->nama }}</span>
                                                    @endif
                                                @else
                                                    <span class="badge badge-success px-2 py-1" title="Divisi Kelompok">
                                                        <i class="fas fa-users mr-1"></i> Kelompok
                                                    </span>
                                                @endif
                                            </td>
                                            <td class="text-center align-middle">
                                                @if($data->status)
                                                    <span class="badge badge-success px-2 py-1"><i class="fas fa-check-circle mr-1"></i> Active</span>
                                                @else
                                                    <span class="badge badge-secondary px-2 py-1"><i class="fas fa-times-circle mr-1"></i> Non Active</span>
                                                @endif
                                            </td>
                                            <td class="text-center align-middle small text-muted">
                                                {{ !empty($data->updated_at) ? $data->updated_at->format('d M Y H:i') : '-' }}
                                            </td>
                                            <td class="text-center align-middle">
                                                <div class="btn-group btn-group-sm" role="group">
                                                    <button type="button" 
                                                            class="btn btn-warning edit-data" 
                                                            data-toggle="modal" 
                                                            data-target="#modalInput"
                                                            data-id="{{ $data->id }}"
                                                            data-nama_lengkap="{{ $data->nama_lengkap }}"
                                                            data-jenis_kelamin="{{ $data->jenis_kelamin }}"
                                                            data-murid_id="{{ $data->murid_id }}"
                                                            data-divisi_nama="{{ !empty($data->getDivisi) ? $data->getDivisi->nama : 'Kelompok' }}"
                                                            data-status="{{ $data->status ? 1 : 0 }}"
                                                            title="Ubah Data">
                                                        <i class="fa fa-edit"></i>
                                                    </button>
                                                    <button type="button" 
                                                            class="btn {{ $data->status ? 'btn-outline-danger' : 'btn-outline-success' }} toggle-status"
                                                            data-id="{{ $data->id }}"
                                                            data-nama="{{ $data->nama_lengkap }}"
                                                            data-status="{{ $data->status ? 1 : 0 }}"
                                                            data-next-action="{{ $data->status ? 'menonaktifkan' : 'mengaktifkan' }}"
                                                            title="{{ $data->status ? 'Nonaktifkan Jamaah' : 'Aktifkan Jamaah' }}">
                                                        <i class="fa {{ $data->status ? 'fa-toggle-on text-success' : 'fa-toggle-off text-muted' }}"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="7" class="text-center text-muted py-4">
                                                <i class="fas fa-users-slash fa-2x mb-2 d-block text-secondary"></i>
                                                Belum ada data personal / jamaah. Klik tombol <strong>Tambah Personal / Jamaah</strong> untuk menambahkan.
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
                    <span id="title">Tambah</span> Data Personal / Jamaah
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form name="input-data" method="POST" action="{{ route('kelompok.master_data.jamaah.create') }}" onsubmit="return validateForm()">
                @csrf
                <div class="modal-body">
                    <input type="hidden" id="id" name="id">

                    <!-- Opsi Pengambilan Sumber Data -->
                    <div class="alert alert-light border mb-3">
                        <div class="form-group mb-0">
                            <label for="murid_id" class="font-weight-bold d-flex justify-content-between align-items-center">
                                <span><i class="fas fa-link text-primary mr-1"></i> Sumber Data (Pilih dari Murid Remaja & Pranikah):</span>
                                <small class="text-muted font-italic">*Opsional: Pilih jika jamaah berasal dari data murid</small>
                            </label>
                            <select class="form-control" id="murid_id" name="murid_id">
                                <option value="" data-nama="" data-jk="" data-divisi="">-- Input Manual (Bukan Murid / Jamaah Umum) --</option>
                                @foreach($listMurid as $murid)
                                    @php
                                        $divisiNama = !empty($murid->getDivisi) ? $murid->getDivisi->nama : 'Umum';
                                        $jkLabel = $murid->jenis_kelamin == 1 ? 'Laki-laki' : 'Perempuan';
                                    @endphp
                                    <option value="{{ $murid->id }}" 
                                            data-nama="{{ $murid->nama_lengkap }}" 
                                            data-jk="{{ $murid->jenis_kelamin }}" 
                                            data-divisi="{{ $divisiNama }}">
                                        [{{ $divisiNama }}] {{ $murid->nama_lengkap }} ({{ $jkLabel }})
                                    </option>
                                @endforeach
                            </select>
                            <div id="sourceInfoBadge" class="mt-2" style="display: none;">
                                <span class="badge badge-success px-2 py-1">
                                    <i class="fas fa-check-circle mr-1"></i> Data ditarik dari Murid Divisi: <span id="labelDivisiTerpilih" class="font-weight-bold"></span>
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-5 form-group">
                            <label for="nama_lengkap">Nama Lengkap <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" placeholder="Contoh: Muhammad Abdullah" id="nama_lengkap" name="nama_lengkap" required>
                            <small class="form-text text-danger error-nama" style="display: none;">Harap isi nama lengkap!</small>
                        </div>
                        <div class="col-md-4 form-group">
                            <label for="jenis_kelamin">Jenis Kelamin <span class="text-danger">*</span></label>
                            <select class="form-control" id="jenis_kelamin" name="jenis_kelamin" required>
                                <option value="1">Laki-laki</option>
                                <option value="0">Perempuan</option>
                            </select>
                            <small class="form-text text-danger error-jk" style="display: none;">Pilih jenis kelamin!</small>
                        </div>
                        <div class="col-md-3 form-group">
                            <label for="status">Status</label>
                            <select class="form-control" id="status" name="status">
                                <option value="1">Active (Aktif)</option>
                                <option value="0">Non Active (Nonaktif)</option>
                            </select>
                        </div>
                    </div>

                    <div class="alert alert-info py-2 px-3 mb-0 small">
                        <i class="fas fa-info-circle mr-1"></i>
                        Jika data dipilih dari murid (Remaja/Pranikah), divisi akan otomatis tersimpan. Jika data input manual, dikategorikan sebagai <strong>Divisi Kelompok</strong>.
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <i class="fas fa-times mr-1"></i> Tutup
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
                    "search": "Cari Jamaah:",
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

    function validateForm() {
        let valid = true;
        const nama = $('#nama_lengkap').val().trim();
        const jk = $('#jenis_kelamin').val();

        if (!nama) {
            $('.error-nama').show();
            $('#nama_lengkap').focus();
            valid = false;
        } else {
            $('.error-nama').hide();
        }

        if (jk === '' || jk === null) {
            $('.error-jk').show();
            valid = false;
        } else {
            $('.error-jk').hide();
        }

        return valid;
    }

    // Event ketika dropdown pilihan murid berubah
    $(document).on('change', '#murid_id', function () {
        const selectedOpt = $(this).find('option:selected');
        const muridId = $(this).val();

        if (muridId) {
            const nama = selectedOpt.data('nama');
            const jk = selectedOpt.data('jk');
            const divisi = selectedOpt.data('divisi');

            if (nama) {
                $('#nama_lengkap').val(nama);
            }
            if (jk !== undefined && jk !== '') {
                $('#jenis_kelamin').val(jk);
            }
            if (divisi) {
                $('#labelDivisiTerpilih').text(divisi);
                $('#sourceInfoBadge').show();
            } else {
                $('#sourceInfoBadge').hide();
            }
        } else {
            $('#sourceInfoBadge').hide();
        }
    });

    $(document).on('click', '#tambahData', function () {
        $('#title').text('Tambah');
        $('#btnSubmit').html('<i class="fas fa-save mr-1"></i> Simpan');
        $('#id').val('');
        $('#murid_id').val('');
        $('#nama_lengkap').val('');
        $('#jenis_kelamin').val('1');
        $('#status').val('1');
        $('#sourceInfoBadge').hide();
        $('.error-nama').hide();
        $('.error-jk').hide();
    });

    $(document).on('click', '.edit-data', function () {
        $('#title').text('Ubah');
        $('#btnSubmit').html('<i class="fas fa-save mr-1"></i> Perbarui');

        const id = $(this).data('id');
        const nama = $(this).data('nama_lengkap');
        const jk = $(this).data('jenis_kelamin');
        const muridId = $(this).data('murid_id');
        const divisiNama = $(this).data('divisi_nama');
        const status = $(this).data('status');

        $('#id').val(id);
        $('#nama_lengkap').val(nama);
        $('#jenis_kelamin').val(jk);
        $('#murid_id').val(muridId || '');
        $('#status').val(status);

        if (divisiNama) {
            $('#labelDivisiTerpilih').text(divisiNama);
            $('#sourceInfoBadge').show();
        } else {
            $('#sourceInfoBadge').hide();
        }

        $('.error-nama').hide();
        $('.error-jk').hide();
    });

    $(document).on('click', '.toggle-status', function (e) {
        e.preventDefault();

        const id = $(this).data('id');
        const nama = $(this).data('nama');
        const actionText = $(this).data('next-action');

        Swal.fire({
            title: "Ubah Status Jamaah?",
            text: "Apakah Anda yakin ingin " + actionText + " data personal/jamaah \"" + nama + "\"?",
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
                    url: "{{ url('/kelompok/master-data/jamaah/toggle-status') }}/" + id,
                    data: { _token: "{{ csrf_token() }}" },
                    success: function (data) {
                        if (data.status === "success") {
                            toastMixin.fire({
                                icon: 'success',
                                title: data.message || ('Status jamaah ' + nama + ' berhasil diperbarui.')
                            });
                            location.reload();
                        } else {
                            toastMixin.fire({
                                icon: 'error',
                                title: data.message || ('Gagal mengubah status jamaah ' + nama)
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

    $(document).on('click', '#btnSyncMurid', function (e) {
        e.preventDefault();

        Swal.fire({
            title: "Sinkronisasi Data Murid?",
            html: "Sistem akan mendeteksi data Murid divisi <b>Remaja & Pranikah</b>.<br><br>" +
                  "<ul class='text-left small mb-0'>" +
                  "<li>Jika belum ada di daftar Jamaah: <b>Otomatis ditambahkan</b>.</li>" +
                  "<li>Jika sudah ada: <b>Data diperbarui</b> (Nama, JK, Divisi, Status) tanpa mengubah pilihan kelompok yang sudah diset.</li>" +
                  "</ul>",
            icon: "info",
            showCancelButton: true,
            confirmButtonColor: "#007bff",
            cancelButtonColor: "#6c757d",
            confirmButtonText: '<i class="fas fa-sync-alt mr-1"></i> Mulai Sinkronisasi',
            cancelButtonText: '<i class="fas fa-times mr-1"></i> Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({
                    title: 'Sedang Sinkronisasi...',
                    html: '<i class="fas fa-spinner fa-spin mr-1"></i> Memproses data murid ke daftar personal jamaah...',
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    showConfirmButton: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                $.ajax({
                    type: "POST",
                    url: "{{ route('kelompok.master_data.jamaah.sync') }}",
                    data: { _token: "{{ csrf_token() }}" },
                    success: function (data) {
                        if (data.status === "success") {
                            Swal.fire({
                                title: 'Sinkronisasi Selesai!',
                                html: `<div class="text-left small">
                                        <p class="mb-2 font-weight-bold text-success">${data.message}</p>
                                        <ul class="mb-0">
                                            <li>Data baru ditambahkan: <b>${data.created}</b></li>
                                            <li>Data diperbarui: <b>${data.updated}</b></li>
                                            <li>Total murid diproses: <b>${data.total}</b></li>
                                        </ul>
                                       </div>`,
                                icon: 'success',
                                confirmButtonColor: '#28a745',
                                confirmButtonText: '<i class="fas fa-check mr-1"></i> Selesai'
                            }).then(() => {
                                location.reload();
                            });
                        } else {
                            Swal.fire({
                                title: 'Gagal',
                                text: data.message || 'Terjadi kesalahan saat sinkronisasi.',
                                icon: 'error'
                            });
                        }
                    },
                    error: function (xhr) {
                        let errMsg = 'Terjadi kesalahan saat memproses data di server.';
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            errMsg = xhr.responseJSON.message;
                        }
                        Swal.fire({
                            title: 'Error',
                            text: errMsg,
                            icon: 'error'
                        });
                    }
                });
            }
        });
    });
</script>
@endsection

