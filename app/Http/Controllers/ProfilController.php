<?php

namespace App\Http\Controllers;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class ProfilController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        $data = Auth::user();

        return view('pages.profil.index', compact('data'));
    }

    public function update($id, $view)
    {
        $data = Auth::user();

        if ($view == 'data') {
            $title = 'Perbarui Data Pribadi';
            return view('pages.profil.edit_data', compact('data', 'view', 'title'));
        } else {
            $title = 'Perbarui Password';
            return view('pages.profil.edit_password', compact('data', 'view', 'title'));
        }
    }

    public function store(Request $request)
    {
        $status = "Berhasil";

        if ($request->view == 'data') {
            $title  = "perbarui data pribadi";
        } else {
            $title  = "mengganti password";
        }

        $data = Auth::user();

        DB::beginTransaction();
        try {
            if ($request->view == 'data') {
                $this->validate($request, [
                    'username'  => 'required',
                    'email'     => 'required|email|unique:users,email,' . $data->id,
                    'nama'      => 'required',
                ]);

                $data->username = $request->username;
                $data->nama     = $request->nama;
                $data->email    = $request->email;
                $data->no_telp  = $request->no_telp;
                $data->save();
            } else {
                $validator = Validator::make($request->all(), [
                    'current_password' => 'required',
                    'new_password'     => 'required|min:8|confirmed',
                ]);

                if ($validator->fails()) {
                    DB::rollBack();
                    return back()->with('error', 'Gagal password baru dengan password confirm tidak sama');
                }

                if (!Hash::check($request->current_password, $data->password)) {
                    DB::rollBack();
                    return back()->with('error', 'Gagal password lama salah.');
                }

                $data->password = Hash::make($request->new_password);
                $data->save();
            }

            DB::commit();

            $message = $status . " " . $title;

            return redirect()->route('profil.index')->with(['success' => $message]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Throwable $th) {
            DB::rollBack();

            return back()->with('error', 'Gagal ' . $title);
        }
    }
}
