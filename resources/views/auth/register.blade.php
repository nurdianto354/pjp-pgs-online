<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>PJP Online</title>
    <!-- Favicon -->
    <link rel="icon" type="image/svg+xml" href="{{ asset('assets/img/favicon.svg') }}">
    <link rel="icon" type="image/png" sizes="64x64" href="{{ asset('assets/img/favicon.png') }}">
    <link rel="shortcut icon" href="{{ asset('favicon.ico') }}">

    <!-- Google Font: Source Sans Pro -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="{{ asset('assets/plugins/fontawesome-free/css/all.min.css') }}">
    <!-- icheck bootstrap -->
    <link rel="stylesheet" href="{{ asset('assets/plugins/icheck-bootstrap/icheck-bootstrap.min.css') }}">
    <!-- Theme style -->
    <link rel="stylesheet" href="{{ asset('assets/css/adminlte.min.css') }}">
</head>

<style>
    .card {
        box-shadow: 0 0 1px rgba(0, 0, 0, .125), 0 3px 5px rgba(0, 0, 0, .2);
        margin-bottom: 1rem;
    }
</style>

<body class="hold-transition register-page">
    <div class="register-box">
        <div class="register-logo">
            <a href="{{ route('login') }}">
                <h4 class="font-weight-bold m-0">PJP Online</h4>
                <h3 class="m-0">Pagesangan II</h3>
            </a>
        </div>

        <div class="card">
            <div class="card-body register-card-body col-6 col-md-12">
                <div class="login-box-msg ">
                    <h3 class="font-weight-bold m-0">Sign Up</h3>
                </div>

                <form action="{{ route('register') }}" method="POST">
                    @csrf
                    <div class="row">
                        <div class="col-12 col-md-12 mb-3">
                            <input type="text" class="form-control @error('username') is-invalid @enderror" id="username" name="username" placeholder="Username" required autocomplete="username">
                            @error('username')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                        <div class="col-12 col-md-12 mb-3">
                            <input type="text" class="form-control @error('nama') is-invalid @enderror" id="nama" name="nama" placeholder="Nama Lengkap" required autocomplete="nama">
                            @error('nama')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                        <div class="col-12 col-md-12 mb-3">
                            <input type="text" class="form-control @error('email') is-invalid @enderror" id="email" name="email" placeholder="Email" required autocomplete="email">
                            @error('email')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                        <div class="col-12 col-md-12 mb-3">
                            <input type="text" class="form-control" id="no_telp" name="no_telp" placeholder="No Telp" data-inputmask="'mask': ['+62 999 9999 99999', '0899-9999-99999']" data-mask autocomplete="no_telp">
                        </div>
                        <div class="col-12 col-md-6 mb-3">
                            <input type="password" class="form-control @error('password') is-invalid @enderror" id="password" name="password" placeholder="Password" required autocomplete="password">
                            @error('password')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                        <div class="col-12 col-md-6 mb-3">
                            <input type="password" class="form-control" id="password-confirm" name="password_confirmation" placeholder="Confirm Password" required autocomplete="password_confirmation">
                        </div>
                    </div>
                    <button type="submit" class="btn btn-success btn-block">Sign Up</button>
                </form>

                <div class="col-12 col-md-12 d-flex justify-content-center mt-4">
                    <p class="mb-0">I already have a account? <a href="{{ route('register') }}">Sign In</a></p>
                </div>
            </div>
        </div>
    </div>

    <!-- jQuery -->
    <script src="{{ asset('assets/plugins/jquery/jquery.min.js') }}"></script>
    <!-- Bootstrap 4 -->
    <script src="{{ asset('assets/plugins/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
    <!-- AdminLTE App -->
    <script src="{{ asset('assets/dist/js/adminlte.min.js?v=3.2.0') }}"></script>
    <!-- InputMask -->
    <script src="{{ asset('assets/plugins/moment/moment.min.js') }}"></script>
    <script src="{{ asset('assets/plugins/inputmask/jquery.inputmask.min.js') }}"></script>
    <script>
        $(function () {
            $('[data-mask]').inputmask()
        })

        $(document).ready(function() {
            $('#no_telp').inputmask();
        });
    </script>
</body>

</html>
