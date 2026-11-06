<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;

class AuthController extends Controller
{
    public function showLogin()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();

            session(['login_timestamp' => now()->format('d M Y, H:i:s') . ' WIB']);
            Cookie::queue('sipus_last_librarian', $request->email, 10080);

            return redirect()->intended(route('bukus.index'))->with('success', 'Selamat datang, Pustakawan SIPUS-Del!');
        }

        return back()->with('error', 'Kredensial login Pustakawan tidak valid.')->withInput();
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('success', 'Sesi Pustakawan telah dihapus dengan aman.');
    }
}