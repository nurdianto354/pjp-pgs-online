<?php
    $user  = Auth::user();
?>
<aside class="main-sidebar elevation-4 sidebar-light-success">
    <a href="{{ route('dashboard') }}" class="brand-link pl-3">
        <span class="elevation-3" style="box-shadow: 0 0 0 rgba(0, 0, 0, 0), 0 0 0 rgba(0, 0, 0, 0) !important">PJP</span>
        <span class="brand-text font-weight-light">Online</span>
    </a>

    <div class="sidebar">
        <div class="user-panel mt-3 pb-3 mb-3 d-flex">
            <div class="image">
                @if (empty($user->photo))
                    <img class="img-circle elevation-2" src="{{ asset('assets/img/blank-profile.png') }}" alt="Foto Profil">
                @endif
            </div>
            <div class="info d-flex justify-content-between w-100">
                <a href="{{ route('profil.index') }}" class="d-block">{{ ucwords(strtolower($user->nama)) }}</a>
                <a href="{{ route('profil.index') }}">
                    <i class="fa-regular fa-pen-to-square fa-sm"></i>
                </a>
            </div>
        </div>

        <nav class="mt-2">
            <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
                <li class="nav-item">
                    <a href="{{ route('dashboard') }}" class="nav-link {{ setActive('dashboard') }}">
                        <i class="nav-icon fas fa-tachometer-alt"></i>
                        <p>Dashboard</p>
                    </a>
                </li>
                @can('master_data')
                <li class="nav-item {{ setActiveMenu('master-data/*') }}">
                    <a href="#" class="nav-link {{ setActive('master-data/*') }}">
                        <i class="nav-icon fas fa-table"></i>
                        <p>
                            Master Data
                            <i class="right fas fa-angle-left"></i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item">
                            <a href="{{ route('master_data.tanggal.index') }}" class="nav-link {{ setActive('master-data/tanggal/*') }}">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Tanggal</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('master_data.materi.index') }}" class="nav-link {{ setActive('master-data/materi/*') }}">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Materi</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('master_data.karakter.index') }}" class="nav-link {{ setActive('master-data/karakter/*') }}">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Karakter</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('master_data.satuan.index') }}" class="nav-link {{ setActive('master-data/satuan/*') }}">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Satuan</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('master_data.divisi.index') }}" class="nav-link {{ setActive('master-data/divisi/*') }}">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Divisi</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('master_data.kelas.index') }}" class="nav-link {{ setActive('master-data/kelas/*') }}">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Kelas</p>
                            </a>
                        </li>
                    </ul>
                </li>
                @endcan
                @can('murid')
                <li class="nav-item">
                    <a href="{{ route('murid.index') }}" class="nav-link {{ setActive('murid/*') }}">
                        <i class="nav-icon fas fa-user-cog"></i>
                        <p>Murid</p>
                    </a>
                </li>
                @endcan
                @can('aktivitas')
                <li class="nav-item {{ setActiveMenu('aktivitas/*') }}">
                    <a href="#" class="nav-link {{ setActive('aktivitas/*') }}">
                        <i class="nav-icon fa-solid fa-calendar-days"></i>
                        <p>
                            Aktivitas
                            <i class="right fas fa-angle-left"></i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item">
                            <a href="{{ route('aktivitas.jadwal.index') }}" class="nav-link {{ setActive('aktivitas/jadwal/*') }}">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Jadwal</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('aktivitas.hari_libur.index') }}" class="nav-link {{ setActive('aktivitas/hari-libur/*') }}">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Hari Libur</p>
                            </a>
                        </li>
                    </ul>
                </li>
                @endcan
                @can('absensi')
                <li class="nav-item">
                    <a href="{{ route('absensi.index') }}" class="nav-link {{ setActive('absensi/*') }}">
                        <i class="nav-icon far fa-calendar-check"></i>
                        <p>Absensi</p>
                    </a>
                </li>
                @endcan
                @can('rekap_absensi')
                    <li class="nav-item">
                        <a href="{{ route('rekap_absensi.index') }}" class="nav-link {{ setActive('rekap-absensi/*') }}">
                            <i class="nav-icon fas fa-calendar"></i>
                            <p>Rekap Absensi</p>
                        </a>
                    </li>
                @endcan
                @can('kurikulum_target')
                <li class="nav-item">
                    <a href="{{ route('kurikulum_target.index') }}" class="nav-link {{ setActive('kurikulum-target/*') }}">
                        <i class="nav-icon fas fa-clipboard-list"></i>
                        <p>Kurikulum & Target</p>
                    </a>
                </li>
                @endcan
                @can('pencapaian_target')
                <li class="nav-item">
                    <a href="{{ route('pencapaian_target.index') }}" class="nav-link {{ setActive('pencapaian-target/*') }}">
                        <i class="nav-icon fa-solid fa-chart-line"></i>
                        <p>Pencapaian Target</p>
                    </a>
                </li>
                @endcan
                @can('kegiatan')
                <li class="nav-item">
                    <a href="{{ route('kegiatan.index') }}" class="nav-link {{ setActive('kegiatan/*') }}">
                        <i class="nav-icon fa-solid fa-laptop-file"></i>
                        <p>Kegiatan</p>
                    </a>
                </li>
                @endcan
                @can('laporan')
                <li class="nav-item {{ setActiveMenu('laporan/*') }}">
                    <a href="#" class="nav-link {{ setActive('laporan/*') }}">
                        <i class="nav-icon fa-solid fa-box-archive"></i>
                        <p>
                            Laporan
                            <i class="right fas fa-angle-left"></i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item">
                            <a href="{{ route('laporan.laporan_kelompok.index') }}" class="nav-link {{ setActive('laporan/laporan-kelompok/*') }}">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Laporan Kelompok</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('laporan.laporan_daerah.index') }}" class="nav-link {{ setActive('laporan/laporan-daerah/*') }}">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Laporan Daerah</p>
                            </a>
                        </li>
                    </ul>
                </li>
                @endcan
                @can('bimbingan_konseling')
                <li class="nav-item {{ setActiveMenu('bimbingan-konseling/*') }}">
                    <a href="#" class="nav-link {{ setActive('bimbingan-konseling/*') }}">
                        <i class="nav-icon fa fa-user-graduate"></i>
                        <p>
                            Bimbingan Konseling
                            <i class="right fas fa-angle-left"></i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item">
                            <a href="{{ route('bimbingan_konseling.laporan_kelompok.index') }}" class="nav-link {{ setActive('bimbingan-konseling/laporan-kelompok/*') }}">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Laporan Kelompok</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('bimbingan_konseling.laporan_desa.index') }}" class="nav-link {{ setActive('bimbingan-konseling/laporan-desa/*') }}">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Laporan Desa</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('bimbingan_konseling.laporan_daerah.index') }}" class="nav-link {{ setActive('bimbingan-konseling/laporan-daerah/*') }}">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Laporan Daerah</p>
                            </a>
                        </li>
                    </ul>
                </li>
                @endcan
                @can('master_user')
                <li class="nav-item {{ setActiveMenu('kelompok/*') }}">
                    <a href="#" class="nav-link {{ setActive('kelompok/*') }}">
                        <i class="nav-icon fa fa-users"></i>
                        <p>
                            Data Kelompok
                            <i class="right fas fa-angle-left"></i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item {{ setActiveMenu('kelompok/master-data*') }}">
                            <a href="#" class="nav-link {{ setActive('kelompok/master-data*') }}">
                                <i class="nav-icon fa fa-database"></i>
                                <p>
                                    Master Data
                                    <i class="right fas fa-angle-left"></i>
                                </p>
                            </a>
                            <ul class="nav nav-treeview">
                                <li class="nav-item">
                                    <a href="{{ route('kelompok.master_data.kegiatan.index') }}" class="nav-link  {{ setActive('kelompok/master-data/kegiatan/*') }}">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>Kegiatan</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="{{ route('kelompok.master_data.lokasi.index') }}" class="nav-link  {{ setActive('kelompok/master-data/lokasi/*') }}">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>Lokasi</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="{{ route('kelompok.master_data.jamaah.index') }}" class="nav-link  {{ setActive('kelompok/master-data/jamaah/*') }}">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>Personal / Jamaah</p>
                                    </a>
                                </li>
                            </ul>
                        </li>
                        <li class="nav-item {{ setActiveMenu('kelompok/jadwal*') }}">
                            <a href="{{ route('kelompok.jadwal.index') }}" class="nav-link {{ setActive('kelompok/jadwal*') }}">
                                <i class="nav-icon fa fa-calendar-alt"></i>
                                <p>Jadwal Kegiatan</p>
                            </a>
                        </li>
                        <li class="nav-item {{ setActiveMenu('kelompok/absensi*') }}">
                            <a href="{{ route('kelompok.absensi.index') }}" class="nav-link {{ setActive('kelompok/absensi*') }}">
                                <i class="nav-icon fas fa-clipboard-check"></i>
                                <p>Absensi Kegiatan</p>
                            </a>
                        </li>
                    </ul>
                </li>
                <li class="nav-item {{ setActiveMenu('master-user/*') }}">
                    <a href="#" class="nav-link {{ setActive('master-user/*') }}">
                        <i class="nav-icon fas fa-users-cog"></i>
                        <p>
                            Master User
                            <i class="right fas fa-angle-left"></i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item">
                            <a href="{{ route('master_user.user.index') }}" class="nav-link  {{ setActive('master-user/user/*') }}">
                                <i class="far fa-circle nav-icon"></i>
                                <p>User</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('master_user.role.index') }}" class="nav-link  {{ setActive('master-user/role/*') }}">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Role</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('master_user.permission.index') }}" class="nav-link  {{ setActive('master-user/permission/*') }}">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Permission</p>
                            </a>
                        </li>
                    </ul>
                </li>
                @endcan
            </ul>
        </nav>
    </div>
</aside>
