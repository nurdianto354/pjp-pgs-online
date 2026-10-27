@extends('layouts.app')

@section('content')
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">Data Kegiatan</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item">Data Kelompok</li>
                    <li class="breadcrumb-item">Master Data</li>
                    <li class="breadcrumb-item active"><a href="{{ route('kelompok.master_data.kegiatan.index') }}">Data Kegiatan</a></li>
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
                            <i class="fas fa-calendar-check mr-1"></i>
                            Master Data Kegiatan
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <button type="button" class="btn btn-success btn-sm" data-toggle="modal" data-target="#modalInput" id="tambahData">
                                <i class="fa fa-plus mr-1"></i> Tambah Kegiatan
                            </button>
                        </div>

                        <div class="table-responsive">
                            <table id="dataTables" class="table table-bordered table-striped table-hover">
                                <thead>
                                    <tr class="text-center">
                                        <th style="width: 5%;">No</th>
                                        <th>Nama Kegiatan</th>
                                        <th style="width: 18%;">Lingkup</th>
                                        <th>Keterangan</th>
                                        <th style="width: 12%;">Status</th>
                                        <th style="width: 15%;">Diperbarui</th>
                                        <th style="width: 18%;">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($datas as $key => $data)
                                        <tr>
                                            <td class="text-center align-middle">{{ ++$key }}</td>
                                            <td class="align-middle font-weight-bold">{{ $data->nama }}</td>
                                            <td class="text-center align-middle">
                                                @if($data->lingkup == 1)
                                                    <span class="badge badge-primary px-2 py-1"><i class="fas fa-sitemap mr-1"></i> Penerobosan Pusat</span>
                                                @elseif($data->lingkup == 2)
                                                    <span class="badge badge-info px-2 py-1"><i class="fas fa-city mr-1"></i> Daerahan</span>
                                                @elseif($data->lingkup == 3)
                                                    <span class="badge badge-teal px-2 py-1" style="background-color: #20c997; color: #fff;"><i class="fas fa-home mr-1"></i> Desa</span>
                                                @else
                                                    <span class="badge badge-light px-2 py-1">-</span>
                                                @endif
                                            </td>
                                            <td class="align-middle text-muted">{{ $data->keterangan ?? '-' }}</td>
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
                                                        data-lingkup="{{ $data->lingkup }}"
                                                        data-keterangan="{{ $data->keterangan }}"
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
                                            <td colspan="7" class="text-center text-muted">Belum ada data kegiatan.</td>
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
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="overlay loading" style="display: none;">
                <div class="d-flex justify-content-center">
                    <div class="spinner-border text-primary" style="width: 3rem; height: 3rem;" role="status">
                        <span class="sr-only">Loading...</span>
                    </div>
                </div>
            </div>
            <div class="modal-header bg-light">
                <h5 class="modal-title font-weight-bold"><span id="title"></span> Data Kegiatan</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form name="input-data" method="POST" action="{{ route('kelompok.master_data.kegiatan.create') }}" onsubmit="return validateForm()">
                @csrf
                <div class="modal-body">
                    <input type="hidden" id="id" name="id">

                    <div class="form-group">
                        <label for="nama">Nama Kegiatan <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" placeholder="Contoh: Penerobosan Pengurus Pusat" id="nama" name="nama" required>
                        <small class="form-text text-danger error-nama" style="display: none;">Harap isi nama kegiatan!</small>
                    </div>

                    <div class="form-group">
                        <label for="lingkup">Lingkup Kegiatan <span class="text-danger">*</span></label>
                        <select class="form-control" id="lingkup" name="lingkup" required>
                            <option value="">-- Pilih Lingkup Kegiatan --</option>
                            <option value="1">1. Penerobosan Pusat</option>
                            <option value="2">2. Daerahan</option>
                            <option value="3">3. Desa</option>
                        </select>
                        <small class="form-text text-danger error-lingkup" style="display: none;">Harap pilih lingkup kegiatan!</small>
                    </div>

                    <div class="form-group">
                        <label for="keterangan">Keterangan</label>
                        <textarea class="form-control" id="keterangan" name="keterangan" rows="3" placeholder="Keterangan tambahan (opsional)"></textarea>
                    </div>

                    <div class="form-group">
                        <label for="status">Status</label>
                        <select class="form-control" id="status" name="status">
                            <option value="1">Active (Aktif)</option>
                            <option value="0">Non Active (Nonaktif)</option>
                        </select>
                        <small class="form-text text-muted">Data yang non-aktif akan dipertahankan dalam sistem.</small>
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
<script>
    $(document).ready(function () {
        $('.loading').hide();
        $('.error-nama').hide();
        $('.error-lingkup').hide();
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

        const lingkup = $('#lingkup').val();
        if (!lingkup || lingkup === '') {
            $('.error-lingkup').show();
            isValid = false;
        } else {
            $('.error-lingkup').hide();
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
        $('#lingkup').val('');
        $('#keterangan').val('');
        $('#status').val('1');

        $('.error-nama').hide();
        $('.error-lingkup').hide();
    });

    $(document).on("click", ".update-data", function () {
        $('.loading').hide();

        $('#btnSave').html('<i class="fas fa-check mr-1"></i> Perbarui');
        $('#title').text('Ubah');

        const id = $(this).data('id');
        const nama = $(this).data('nama');
        const lingkup = $(this).data('lingkup');
        const keterangan = $(this).data('keterangan');
        const status = $(this).data('status');

        $('#id').val(id);
        $('#nama').val(nama);
        $('#lingkup').val(lingkup);
        $('#keterangan').val(keterangan);
        $('#status').val(status);

        $('.error-nama').hide();
        $('.error-lingkup').hide();
    });

    $(document).on('click', '.toggle-status', function(e) {
        e.preventDefault();

        const id = $(this).data('id');
        const nama = $(this).data('nama');
        const actionText = $(this).data('next-action');

        Swal.fire({
            title: "Ubah Status Kegiatan?",
            text: "Apakah Anda yakin ingin " + actionText + " data kegiatan \"" + nama + "\"?",
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
                    url: "{{ url('/kelompok/master-data/kegiatan/toggle-status') }}/" + id,
                    data: {
                        _token: "{{ csrf_token() }}"
                    },
                    success: function(data) {
                        if (data.status === "success") {
                            toastMixin.fire({
                                icon: 'success',
                                title: data.message || ('Status kegiatan ' + nama + ' berhasil diperbarui.'),
                            });
                            location.reload();
                        } else {
                            toastMixin.fire({
                                icon: 'error',
                                title: data.message || ('Gagal mengubah status kegiatan ' + nama),
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

