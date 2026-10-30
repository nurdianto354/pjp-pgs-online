<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use Illuminate\Foundation\Auth\AuthenticatesUsers;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = RouteServiceProvider::HOME;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }

    /**
     * Override method login untuk diagnosa langsung di browser.
     */
    public function login(\Illuminate\Http\Request $request)
    {
        // 1. CEK APAKAH DATA DARI FORM SAMPAI KE SERVER
        dd([
            'CEK PAYLOAD DARI BROWSER' => [
                'Method_Request' => $request->method(),
                'Is_Secure_HTTPS' => $request->isSecure() ? 'YA (HTTPS)' : 'TIDAK (Masih HTTP)',
                'URL_Tujuan_Form' => $request->fullUrl(),
                'Headers_Content_Type' => $request->header('Content-Type'),
                'Semua_Input_Diterima' => $request->all(),
            ]
        ]);

        $this->validateLogin($request);

        $username = $request->input($this->username());
        $password = $request->input('password');

        $user = \App\Models\User::where($this->username(), $username)->first();
        $isPasswordMatch = $user ? \Illuminate\Support\Facades\Hash::check($password, $user->password) : false;
        $attempt = $this->attemptLogin($request);

        // Langsung tampilkan hasil evaluasi otentikasi di layar browser
        dd([
            'DIAGNOSA LOGIN' => [
                '1. Status Auth Attempt' => $attempt ? 'BERHASIL LOGIN' : 'GAGAL LOGIN (Kredensial Ditolak)',
                '2. Username Diinput'   => $username,
                '3. User Ditemukan di DB'=> $user ? 'YA (ID: ' . $user->id . ')' : 'TIDAK DITEMUKAN',
                '4. Password Cocok'      => $isPasswordMatch ? 'COCOK' : 'TIDAK COCOK',
                '5. Target Redirect URL' => redirect()->intended($this->redirectPath())->getTargetUrl(),
                '6. Session ID Aktif'    => session()->getId(),
                '7. Driver Session'      => config('session.driver'),
            ]
        ]);
    }
}
