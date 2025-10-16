<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function showLoginForm()
    {
        return view('auth.login'); // nanti kita bikin view-nya
    }

    public function login(Request $request)
    {
        $username = $request->input('username');
        $password = $request->input('password');

        $validUser = config('auth_custom.user');
        $validPass = config('auth_custom.pass');
        // dump($validUser, $validPass);
        // dd($username, $password);
        if ($username === $validUser && $password === $validPass) {
            session(['is_logged_in' => true]);
            return redirect()->route('monitoring.mutasi_berkas.index');
        }

        return back()->withErrors(['login' => 'Username atau password salah.']);
    }

    public function logout()
    {
        session()->forget('is_logged_in');
        return redirect()->route('login');
    }
}
