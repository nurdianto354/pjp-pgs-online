<?php

use App\Http\Controllers\AbsensiController;
use App\Http\Controllers\Aktivitas\HariLiburController;
use App\Http\Controllers\Aktivitas\JadwalController;
use App\Http\Controllers\BimbinganKonseling\LaporanDaerahController;
use App\Http\Controllers\BimbinganKonseling\LaporanDesaController;
use App\Http\Controllers\BimbinganKonseling\LaporanKelompokController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\KegiatanController;
use App\Http\Controllers\Kelompok\MasterData\KegiatanController as KelompokKegiatanController;
use App\Http\Controllers\Kelompok\MasterData\KelompokController;
use App\Http\Controllers\Kelompok\MasterData\LokasiController;
use App\Http\Controllers\Kelompok\MasterData\JamaahController;
use App\Http\Controllers\Kelompok\MasterData\PengajianController;
use App\Http\Controllers\Kelompok\JadwalController as KelompokJadwalController;
use App\Http\Controllers\Kelompok\AbsensiKelompokController;
use App\Http\Controllers\KurikulumTargetController;
use App\Http\Controllers\Laporan\LaporanLaporanDaerahController;
use App\Http\Controllers\Laporan\LaporanLaporanKelompokController;
use App\Http\Controllers\MasterData\DivisiController;
use App\Http\Controllers\MasterData\KarakterController;
use App\Http\Controllers\MasterData\KelasController;
use App\Http\Controllers\MasterData\MateriController;
use App\Http\Controllers\MasterData\SatuanController;
use App\Http\Controllers\MasterData\TanggalController;
use App\Http\Controllers\MasterUser\PermissionController;
use App\Http\Controllers\MasterUser\RoleController;
use App\Http\Controllers\MasterUser\UserController;
use App\Http\Controllers\MuridController;
use App\Http\Controllers\PencapaianTargetController;
use App\Http\Controllers\ProfilController;
use App\Http\Controllers\RekapAbsensiController;
use App\Models\Kelompok\MasterData\Pengajian;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Auth::routes([
    'register' => false, // Registration Routes...
    'reset' => false, // Password Reset Routes...
    'verify' => false, // Email Verification Routes...
]);

// Route Publik Presensi Kelompok (Akses Bebas Tanpa Login)
Route::prefix('presensi-kelompok')->group(function () {
    Route::get('{jadwal_id}', [AbsensiKelompokController::class, 'publicForm'])->name('kelompok.absensi.public_form');
    Route::get('{jadwal_id}/jamaah', [AbsensiKelompokController::class, 'getJamaahList'])->name('kelompok.absensi.get_jamaah');
    Route::post('{jadwal_id}/submit', [AbsensiKelompokController::class, 'publicStore'])->name('kelompok.absensi.public_store');
    Route::get('{jadwal_id}/rekap', [AbsensiKelompokController::class, 'publicRekap'])->name('kelompok.absensi.public_rekap');
});

Route::group(['middleware' => 'auth'], function() {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    Route::controller(ProfilController::class)->prefix('profil')->group(function () {
        Route::get('index', 'index')->name('profil.index');
        Route::get('update/{id}/{view}', 'update')->name('profil.update');
        Route::put('store', 'store')->name('profil.store');
    });

    Route::prefix('master-data')->group(function () {
        Route::controller(TanggalController::class)->prefix('tanggal')->group(function () {
            Route::get('index', 'index')->name('master_data.tanggal.index');
            Route::post('create', 'create')->name('master_data.tanggal.create');
            Route::post('delete/{id}', 'destroy')->name('master_data.tanggal.destroy');
        });

        Route::controller(MateriController::class)->prefix('materi')->group(function () {
            Route::get('index', 'index')->name('master_data.materi.index');
            Route::post('create', 'create')->name('master_data.materi.create');
            Route::post('delete/{id}', 'destroy')->name('master_data.materi.destroy');
        });

        Route::controller(KarakterController::class)->prefix('karakter')->group(function () {
            Route::get('index', 'index')->name('master_data.karakter.index');
            Route::post('create', 'create')->name('master_data.karakter.create');
            Route::post('delete/{id}', 'destroy')->name('master_data.karakter.destroy');
        });

        Route::controller(SatuanController::class)->prefix('satuan')->group(function () {
            Route::get('index', 'index')->name('master_data.satuan.index');
            Route::post('create', 'create')->name('master_data.satuan.create');
            Route::post('delete/{id}', 'destroy')->name('master_data.satuan.destroy');
        });

        Route::controller(DivisiController::class)->prefix('divisi')->group(function () {
            Route::get('index', 'index')->name('master_data.divisi.index');
            Route::post('create', 'create')->name('master_data.divisi.create');
            Route::post('delete/{id}', 'destroy')->name('master_data.divisi.destroy');
        });

        Route::controller(KelasController::class)->prefix('kelas')->group(function () {
            Route::get('index', 'index')->name('master_data.kelas.index');
            Route::post('create', 'create')->name('master_data.kelas.create');
            Route::post('delete/{id}', 'destroy')->name('master_data.kelas.destroy');
        });
    });

    Route::controller(MuridController::class)->prefix('murid')->group(function () {
        Route::get('index', 'index')->name('murid.index');
        Route::post('create', 'create')->name('murid.create');
        Route::post('delete/{id}', 'destroy')->name('murid.destroy');
        Route::get('export-excel', 'exportExcel')->name('murid.export_excel');
    });

    Route::controller(KurikulumTargetController::class)->prefix('kurikulum-target')->group(function () {
        Route::get('index', 'index')->name('kurikulum_target.index');
        Route::get('create', 'create')->name('kurikulum_target.create');
        Route::get('data-detail', 'getDataDetail')->name('kurikulum_target.data_detail');
        Route::put('store', 'store')->name('kurikulum_target.store');
        Route::delete('delete/{id}', 'destroy')->name('kurikulum_target.destroy');
        Route::get('export-template', 'exportTemplate')->name('kurikulum_target.export_template');
        Route::post('import-data', 'importData')->name('kurikulum_target.import_data');
    });

    Route::controller(PencapaianTargetController::class)->prefix('pencapaian-target')->group(function () {
        Route::get('index', 'index')->name('pencapaian_target.index');
        Route::post('store', 'store')->name('pencapaian_target.store');
    });

    Route::prefix('aktivitas')->group(function () {
        Route::controller(JadwalController::class)->prefix('jadwal')->group(function () {
            Route::get('index', 'index')->name('aktivitas.jadwal.index');
            Route::post('create', 'create')->name('aktivitas.jadwal.create');
            Route::post('delete/{id}', 'destroy')->name('aktivitas.jadwal.destroy');
        });

        Route::controller(HariLiburController::class)->prefix('hari-libur')->group(function () {
            Route::get('index', 'index')->name('aktivitas.hari_libur.index');
            Route::post('create', 'create')->name('aktivitas.hari_libur.create');
            Route::post('delete/{id}', 'destroy')->name('aktivitas.hari_libur.destroy');
        });
    });

    Route::controller(AbsensiController::class)->prefix('absensi')->group(function () {
        Route::get('index', 'index')->name('absensi.index');
        Route::post('store', 'store')->name('absensi.store');
    });

    Route::controller(RekapAbsensiController::class)->prefix('rekap-absensi')->group(function () {
        Route::get('index', 'index')->name('rekap_absensi.index');
        Route::get('detail', 'detail')->name('rekap_absensi.detail');
    });

    Route::controller(KegiatanController::class)->prefix('kegiatan')->group(function () {
        Route::get('index', 'index')->name('kegiatan.index');
    });


    Route::prefix('laporan')->group(function () {
        Route::controller(LaporanLaporanKelompokController::class)->prefix('laporan-kelompok')->group(function () {
            Route::get('index', 'index')->name('laporan.laporan_kelompok.index');
        });
        Route::controller(LaporanLaporanDaerahController::class)->prefix('laporan-daerah')->group(function () {
            Route::get('index', 'index')->name('laporan.laporan_daerah.index');
            Route::get('export-excel', 'exportExcel')->name('laporan.laporan_daerah.export_excel');
        });
    });

    Route::prefix('bimbingan-konseling')->group(function () {
        Route::controller(LaporanKelompokController::class)->prefix('laporan-kelompok')->group(function () {
            Route::get('index', 'index')->name('bimbingan_konseling.laporan_kelompok.index');
            Route::get('create/{id?}', 'create')->name('bimbingan_konseling.laporan_kelompok.create');
            Route::get('get-kelas', 'getKelas')->name('bimbingan_konseling.laporan_kelompok.get_kelas');
            Route::put('store', 'store')->name('bimbingan_konseling.laporan_kelompok.store');
            Route::post('delete/{id}', 'destroy')->name('bimbingan_konseling.laporan_kelompok.destroy');
            Route::get('export-excel', 'exportExcel')->name('bimbingan_konseling.laporan_kelompok.export_excel');
            Route::get('export-pdf', 'exportPdf')->name('bimbingan_konseling.laporan_kelompok.export_pdf');
        });
        Route::controller(LaporanDesaController::class)->prefix('laporan-desa')->group(function () {
            Route::get('index', 'index')->name('bimbingan_konseling.laporan_desa.index');
            Route::get('create/{id?}', 'create')->name('bimbingan_konseling.laporan_desa.create');
            Route::put('store', 'store')->name('bimbingan_konseling.laporan_desa.store');
            Route::post('delete/{id}', 'destroy')->name('bimbingan_konseling.laporan_desa.destroy');
            Route::get('export-excel', 'exportExcel')->name('bimbingan_konseling.laporan_desa.export_excel');
        });
        Route::controller(LaporanDaerahController::class)->prefix('laporan-daerah')->group(function () {
            Route::get('index', 'index')->name('bimbingan_konseling.laporan_daerah.index');
            Route::get('create/{id?}', 'create')->name('bimbingan_konseling.laporan_daerah.create');
            Route::put('store', 'store')->name('bimbingan_konseling.laporan_daerah.store');
            Route::post('delete/{id}', 'destroy')->name('bimbingan_konseling.laporan_daerah.destroy');
            Route::get('export-excel', 'exportExcel')->name('bimbingan_konseling.laporan_daerah.export_excel');
        });
    });


    Route::prefix('kelompok')->group(function () {
        Route::prefix('master-data')->group(function () {
            Route::controller(KelompokKegiatanController::class)->prefix('kegiatan')->group(function () {
                Route::get('index', 'index')->name('kelompok.master_data.kegiatan.index');
                Route::post('create', 'create')->name('kelompok.master_data.kegiatan.create');
                Route::post('toggle-status/{id}', 'toggleStatus')->name('kelompok.master_data.kegiatan.toggle_status');
            });
            Route::controller(LokasiController::class)->prefix('lokasi')->group(function () {
                Route::get('index', 'index')->name('kelompok.master_data.lokasi.index');
                Route::post('create', 'create')->name('kelompok.master_data.lokasi.create');
                Route::post('toggle-status/{id}', 'toggleStatus')->name('kelompok.master_data.lokasi.toggle_status');
                Route::post('parse-maps-url', 'parseMapsUrl')->name('kelompok.master_data.lokasi.parse_maps_url');
            });
            Route::controller(JamaahController::class)->prefix('jamaah')->group(function () {
                Route::get('index', 'index')->name('kelompok.master_data.jamaah.index');
                Route::post('create', 'create')->name('kelompok.master_data.jamaah.create');
                Route::post('toggle-status/{id}', 'toggleStatus')->name('kelompok.master_data.jamaah.toggle_status');
                Route::post('sync', 'sync')->name('kelompok.master_data.jamaah.sync');
            });
            Route::controller(KelompokController::class)->prefix('kelompok')->group(function () {
                Route::get('index', 'index')->name('kelompok.master_data.kelompok.index');
                Route::post('create', 'create')->name('kelompok.master_data.kelompok.create');
                Route::post('delete/{id}', 'destroy')->name('kelompok.master_data.kelompok.destroy');
            });
            Route::controller(PengajianController::class)->prefix('pengajian')->group(function () {
                Route::get('index', 'index')->name('kelompok.master_data.pengajian.index');
                Route::post('create', 'create')->name('kelompok.master_data.pengajian.create');
                Route::post('delete/{id}', 'destroy')->name('kelompok.master_data.pengajian.destroy');
            });
        });

        Route::controller(KelompokJadwalController::class)->prefix('jadwal')->group(function () {
            Route::get('index', 'index')->name('kelompok.jadwal.index');
            Route::post('create', 'create')->name('kelompok.jadwal.create');
            Route::post('toggle-status/{id}', 'toggleStatus')->name('kelompok.jadwal.toggle_status');
        });

        Route::controller(AbsensiKelompokController::class)->prefix('absensi')->group(function () {
            Route::get('index', 'index')->name('kelompok.absensi.index');
            Route::get('detail/{jadwal_id}', 'detail')->name('kelompok.absensi.detail');
            Route::post('delete/{id}', 'destroy')->name('kelompok.absensi.destroy');
        });
    });

    Route::prefix('master-user')->group(function () {
        Route::controller(UserController::class)->prefix('user')->group(function () {
            Route::get('index', 'index')->name('master_user.user.index');
            Route::get('create', 'create')->name('master_user.user.create');
            Route::get('update/{id}', 'update')->name('master_user.user.update');
            Route::post('store', 'store')->name('master_user.user.store');
            Route::post('delete/{id}', 'destroy')->name('master_user.user.destroy');
        });

        Route::controller(RoleController::class)->prefix('role')->group(function () {
            Route::get('index', 'index')->name('master_user.role.index');
            Route::post('create', 'create')->name('master_user.role.create');
            Route::post('delete/{id}', 'destroy')->name('master_user.role.destroy');

            Route::get('set-akses/{id}', 'setAkses')->name('master_user.role.set_akses');
            Route::put('store', 'store')->name('master_user.role.store');
        });

        Route::controller(PermissionController::class)->prefix('permission')->group(function () {
            Route::get('index', 'index')->name('master_user.permission.index');
            Route::post('create', 'create')->name('master_user.permission.create');
            Route::post('delete/{id}', 'destroy')->name('master_user.permission.destroy');
        });
    });
});
