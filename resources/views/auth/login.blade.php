<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
    <title>Masuk - PJP Online Pagesangan II</title>

    <!-- Google Fonts: Poppins & Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="{{ asset('assets/plugins/fontawesome-free/css/all.min.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    
    <!-- Bootstrap 4.6 -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">

    <style>
        :root {
            --primary-green: #1e7e34;
            --dark-green: #0d4a2d;
            --deep-green: #08291a;
            --accent-emerald: #28a745;
            --accent-light: #eafaf1;
            --text-dark: #1f2d3d;
            --text-muted: #6c757d;
        }

        * {
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #072215 0%, #0d4a2d 50%, #156239 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px 15px;
            color: var(--text-dark);
            position: relative;
            overflow-x: hidden;
        }

        /* Subtle animated background orbs */
        .bg-orb {
            position: fixed;
            border-radius: 50%;
            filter: blur(80px);
            z-index: 0;
            pointer-events: none;
            opacity: 0.25;
        }
        .bg-orb-1 {
            width: 380px;
            height: 380px;
            background: #28a745;
            top: -100px;
            left: -100px;
        }
        .bg-orb-2 {
            width: 450px;
            height: 450px;
            background: #20c997;
            bottom: -150px;
            right: -100px;
        }

        /* Main Login Card */
        .login-wrapper {
            position: relative;
            z-index: 1;
            width: 100%;
            max-width: 960px;
            background: #ffffff;
            border-radius: 24px;
            box-shadow: 0 25px 60px rgba(0, 0, 0, 0.35);
            overflow: hidden;
            display: flex;
            flex-wrap: wrap;
            margin: auto;
        }

        /* Left Side: Brand Visual */
        .brand-side {
            background: linear-gradient(145deg, #0b3d24 0%, #17653b 100%);
            color: #ffffff;
            padding: 48px 40px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            position: relative;
            overflow: hidden;
        }
        .brand-side::before {
            content: '';
            position: absolute;
            top: -50px;
            right: -50px;
            width: 200px;
            height: 200px;
            background: rgba(255, 255, 255, 0.05);
            border-radius: 50%;
        }
        .brand-side::after {
            content: '';
            position: absolute;
            bottom: -80px;
            left: -50px;
            width: 250px;
            height: 250px;
            background: rgba(40, 167, 69, 0.2);
            border-radius: 50%;
        }

        .brand-logo-icon {
            width: 64px;
            height: 64px;
            background: rgba(255, 255, 255, 0.15);
            backdrop-filter: blur(10px);
            border: 2px solid rgba(255, 255, 255, 0.25);
            border-radius: 18px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 28px;
            color: #ffffff;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
            margin-bottom: 24px;
        }

        .brand-title {
            font-size: 2rem;
            font-weight: 800;
            line-height: 1.2;
            letter-spacing: -0.5px;
            color: #ffffff;
            margin-bottom: 6px;
        }
        .brand-subtitle-badge {
            display: inline-block;
            background: rgba(255, 255, 255, 0.2);
            border: 1px solid rgba(255, 255, 255, 0.3);
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
            letter-spacing: 0.5px;
            text-transform: uppercase;
            margin-bottom: 16px;
        }
        .brand-tagline {
            font-size: 0.95rem;
            line-height: 1.6;
            color: rgba(255, 255, 255, 0.85);
            margin-bottom: 30px;
        }

        .feature-list {
            list-style: none;
            padding: 0;
            margin: 0 0 30px 0;
        }
        .feature-item {
            display: flex;
            align-items: center;
            font-size: 0.9rem;
            color: rgba(255, 255, 255, 0.9);
            margin-bottom: 14px;
        }
        .feature-icon-box {
            width: 32px;
            height: 32px;
            border-radius: 8px;
            background: rgba(255, 255, 255, 0.12);
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 12px;
            font-size: 14px;
            color: #82e0aa;
        }

        .brand-footer-info {
            font-size: 0.8rem;
            color: rgba(255, 255, 255, 0.6);
            border-top: 1px solid rgba(255, 255, 255, 0.15);
            padding-top: 16px;
        }

        /* Right Side: Form Card */
        .form-side {
            padding: 48px 44px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            background: #ffffff;
        }

        .form-header-badge {
            display: inline-block;
            background: var(--accent-light);
            color: var(--primary-green);
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 12px;
        }
        .form-title {
            font-size: 1.75rem;
            font-weight: 700;
            color: var(--text-dark);
            margin-bottom: 6px;
        }
        .form-subtitle {
            font-size: 0.9rem;
            color: var(--text-muted);
            margin-bottom: 28px;
        }

        /* Input Controls */
        .input-group-custom {
            position: relative;
            margin-bottom: 20px;
        }
        .input-group-custom label {
            font-size: 0.85rem;
            font-weight: 600;
            color: #374151;
            margin-bottom: 6px;
            display: block;
        }
        .input-wrapper {
            position: relative;
            display: flex;
            align-items: center;
        }
        .input-icon-left {
            position: absolute;
            left: 16px;
            color: #9ca3af;
            font-size: 16px;
            transition: color 0.2s;
            pointer-events: none;
            z-index: 3;
        }
        .form-control-custom {
            width: 100%;
            height: 48px;
            padding: 10px 16px 10px 46px;
            font-size: 0.95rem;
            font-family: 'Inter', sans-serif;
            color: #111827;
            background-color: #f9fafb;
            border: 1.5px solid #e5e7eb;
            border-radius: 12px;
            transition: all 0.2s ease-in-out;
        }
        .form-control-custom:focus {
            background-color: #ffffff;
            border-color: var(--accent-emerald);
            outline: none;
            box-shadow: 0 0 0 4px rgba(40, 167, 69, 0.15);
        }
        .input-wrapper:focus-within .input-icon-left {
            color: var(--primary-green);
        }

        /* Toggle Password Button */
        .btn-toggle-password {
            position: absolute;
            right: 14px;
            background: transparent;
            border: none;
            color: #9ca3af;
            cursor: pointer;
            padding: 6px;
            font-size: 16px;
            transition: color 0.2s;
            z-index: 4;
        }
        .btn-toggle-password:hover {
            color: var(--primary-green);
        }
        .btn-toggle-password:focus {
            outline: none;
        }

        /* Remember Me & Checkbox */
        .remember-wrapper {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 24px;
            font-size: 0.875rem;
        }
        .custom-checkbox-container {
            display: flex;
            align-items: center;
            cursor: pointer;
            user-select: none;
            margin: 0;
            color: #4b5563;
        }
        .custom-checkbox-container input {
            cursor: pointer;
            width: 17px;
            height: 17px;
            margin-right: 8px;
            accent-color: var(--primary-green);
        }

        /* Submit Button */
        .btn-submit-login {
            width: 100%;
            height: 50px;
            background: linear-gradient(135deg, #28a745 0%, #1e7e34 100%);
            color: #ffffff;
            border: none;
            border-radius: 12px;
            font-size: 1rem;
            font-weight: 600;
            letter-spacing: 0.3px;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 6px 18px rgba(30, 126, 52, 0.35);
            transition: all 0.25s ease-in-out;
            cursor: pointer;
        }
        .btn-submit-login:hover {
            background: linear-gradient(135deg, #218838 0%, #19692c 100%);
            box-shadow: 0 8px 22px rgba(30, 126, 52, 0.45);
            transform: translateY(-2px);
            color: #ffffff;
        }
        .btn-submit-login:active {
            transform: translateY(0);
        }
        .btn-submit-login:disabled {
            opacity: 0.75;
            cursor: not-allowed;
            transform: none;
        }

        /* Public Presensi Shortcut Card */
        .public-shortcut-box {
            margin-top: 24px;
            padding: 14px 16px;
            border-radius: 14px;
            background: #f8fafc;
            border: 1px dashed #cbd5e1;
            text-align: center;
            font-size: 0.85rem;
        }
        .public-shortcut-btn {
            font-weight: 600;
            color: var(--primary-green);
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            transition: all 0.2s;
        }
        .public-shortcut-btn:hover {
            color: #0f5132;
            text-decoration: underline;
        }

        .footer-copyright {
            margin-top: 28px;
            text-align: center;
            font-size: 0.78rem;
            color: #9ca3af;
        }

        /* Responsive Breakpoints */
        @media (max-width: 991.98px) {
            .brand-side {
                padding: 36px 30px;
            }
            .form-side {
                padding: 36px 30px;
            }
        }
        @media (max-width: 767.98px) {
            body {
                padding: 15px 10px;
            }
            .login-wrapper {
                border-radius: 20px;
            }
            .brand-side {
                padding: 28px 24px 20px 24px;
                text-align: center;
                align-items: center;
            }
            .brand-logo-icon {
                margin: 0 auto 16px auto;
            }
            .brand-title {
                font-size: 1.6rem;
            }
            .feature-list, .brand-footer-info {
                display: none;
            }
            .brand-tagline {
                margin-bottom: 0;
                font-size: 0.85rem;
            }
            .form-side {
                padding: 28px 24px;
            }
            .form-title {
                font-size: 1.45rem;
            }
        }
    </style>
</head>

<body>
    <!-- Background Light Orbs -->
    <div class="bg-orb bg-orb-1"></div>
    <div class="bg-orb bg-orb-2"></div>

    <div class="login-wrapper">
        <!-- SISI KIRI: BRANDING & FITUR -->
        <div class="col-12 col-md-5 brand-side">
            <div>
                <div class="brand-logo-icon shadow">
                    <i class="fas fa-mosque"></i>
                </div>
                <h1 class="brand-title">PJP Online</h1>
                <div class="brand-subtitle-badge">
                    <i class="fas fa-map-pin mr-1"></i> Pagesangan II
                </div>
                <p class="brand-tagline">
                    Sistem informasi terpadu pembinaan generus, pengelolaan data jamaah, dan presensi geofencing.
                </p>

                <ul class="feature-list">
                    <li class="feature-item">
                        <div class="feature-icon-box">
                            <i class="fas fa-users-cog"></i>
                        </div>
                        <div>
                            <strong>Data Jamaah Terpusat</strong>
                            <div class="small opacity-75">Kelola data murid & jamaah kelompok</div>
                        </div>
                    </li>
                    <li class="feature-item">
                        <div class="feature-icon-box">
                            <i class="fas fa-map-marked-alt"></i>
                        </div>
                        <div>
                            <strong>Presensi GPS Geofencing</strong>
                            <div class="small opacity-75">Validasi jarak radius lokasi kegiatan</div>
                        </div>
                    </li>
                    <li class="feature-item">
                        <div class="feature-icon-box">
                            <i class="fas fa-chart-line"></i>
                        </div>
                        <div>
                            <strong>Rekapitulasi Realtime</strong>
                            <div class="small opacity-75">Pantau kehadiran jamaah secara live</div>
                        </div>
                    </li>
                </ul>
            </div>

            <div class="brand-footer-info">
                <i class="fas fa-shield-alt mr-1"></i> Lingkungan Aplikasi Aman & Terenkripsi
            </div>
        </div>

        <!-- SISI KANAN: FORMULIR LOGIN -->
        <div class="col-12 col-md-7 form-side">
            <div>
                <span class="form-header-badge">
                    <i class="fas fa-user-lock mr-1"></i> Portal Pengurus & Admin
                </span>
                <h2 class="form-title">Selamat Datang Kembali</h2>
                <p class="form-subtitle">Silakan masukkan akun Anda untuk mengakses dashboard manajemen.</p>

                @if (session('status'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {{ session('status') }}
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                @endif

                <form method="POST" action="{{ route('login') }}" id="loginForm">
                    @csrf

                    <!-- Input Username -->
                    <div class="input-group-custom">
                        <label for="username">Username Akun</label>
                        <div class="input-wrapper">
                            <i class="fas fa-user input-icon-left"></i>
                            <input type="text" 
                                   class="form-control-custom @error('username') is-invalid @enderror" 
                                   placeholder="Ketik username Anda" 
                                   id="username" 
                                   name="username" 
                                   value="{{ old('username') }}" 
                                   required 
                                   autocomplete="username" 
                                   autofocus>
                        </div>
                        @error('username')
                            <div class="text-danger small mt-1 font-weight-bold">
                                <i class="fas fa-exclamation-circle mr-1"></i> {{ $message }}
                            </div>
                        @enderror
                    </div>

                    <!-- Input Password dengan Toggle Show/Hide -->
                    <div class="input-group-custom">
                        <label for="password">Kata Sandi (Password)</label>
                        <div class="input-wrapper">
                            <i class="fas fa-lock input-icon-left"></i>
                            <input type="password" 
                                   class="form-control-custom @error('password') is-invalid @enderror" 
                                   placeholder="Ketik kata sandi Anda" 
                                   id="password" 
                                   name="password" 
                                   required 
                                   autocomplete="current-password"
                                   style="padding-right: 48px;">
                            <button type="button" class="btn-toggle-password" id="btnTogglePassword" title="Tampilkan/Sembunyikan Sandi" tabindex="-1">
                                <i class="far fa-eye" id="togglePasswordIcon"></i>
                            </button>
                        </div>
                        @error('password')
                            <div class="text-danger small mt-1 font-weight-bold">
                                <i class="fas fa-exclamation-circle mr-1"></i> {{ $message }}
                            </div>
                        @enderror
                    </div>

                    <!-- Remember Me Checkbox -->
                    <div class="remember-wrapper">
                        <label class="custom-checkbox-container">
                            <input type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}>
                            <span>Ingat saya di perangkat ini</span>
                        </label>
                    </div>

                    <!-- Tombol Masuk -->
                    <button type="submit" class="btn-submit-login" id="btnSubmitLogin">
                        <i class="fas fa-sign-in-alt mr-2"></i> Masuk ke Sistem
                    </button>
                </form>

                <!-- Shortcut Presensi Publik (Jika Ada Jadwal Aktif) -->
                @php
                    $latestJadwal = \App\Models\Kelompok\Jadwal::where('status', true)->latest('tanggal')->first();
                @endphp
                @if($latestJadwal)
                    <div class="public-shortcut-box">
                        <div class="text-muted mb-1">Bukan pengurus dan ingin mengisi absensi?</div>
                        <a href="{{ route('kelompok.absensi.public_form', $latestJadwal->id) }}" class="public-shortcut-btn font-weight-bold">
                            <i class="fas fa-clipboard-check"></i> Form Presensi Kegiatan Kelompok Terkini &rarr;
                        </a>
                    </div>
                @endif

                <div class="footer-copyright">
                    &copy; {{ date('Y') }} PJP Online Pagesangan II &bull; All rights reserved.
                </div>
            </div>
        </div>
    </div>

    <!-- Script jQuery, Bootstrap, FontAwesome -->
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.5.1/dist/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        $(document).ready(function () {
            // Toggle Show / Hide Password
            $('#btnTogglePassword').on('click', function (e) {
                e.preventDefault();
                const passwordInput = $('#password');
                const icon = $('#togglePasswordIcon');

                if (passwordInput.attr('type') === 'password') {
                    passwordInput.attr('type', 'text');
                    icon.removeClass('fa-eye').addClass('fa-eye-slash text-success');
                } else {
                    passwordInput.attr('type', 'password');
                    icon.removeClass('fa-eye-slash text-success').addClass('fa-eye');
                }
            });

            // Loading state saat tombol masuk diklik
            $('#loginForm').on('submit', function () {
                const btn = $('#btnSubmitLogin');
                btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-2"></i> Memvalidasi Akun...');
            });
        });
    </script>
</body>
</html>
