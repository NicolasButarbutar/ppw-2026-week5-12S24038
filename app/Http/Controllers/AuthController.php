<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use Illuminate\View\View;

class AuthController extends Controller
{
    /**
     * Menampilkan formulir login pustakawan.
     */
    public function showLoginForm(Request $request): View
    {
        $rememberedEmail = $request->cookie('del_last_user_email');
        return view('auth.login', compact('rememberedEmail'));
    }

    /**
     * Memproses otentikasi login pengguna dengan regenerasi sesi anti-Session Fixation.
     */
    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ], [
            'email.required' => 'Alamat email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'password.required' => 'Kata sandi wajib diisi.',
        ]);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            // WAJIB: Regenerasi ID sesi guna menangkal serangan Session Fixation
            $request->session()->regenerate();

            // Simpan timestamp login pada state sesi pengguna
            session(['login_timestamp' => now()->format('d M Y, H:i:s') . ' WIB']);

            // Simpan email terakhir di HTTP Cookie (HttpOnly & Secure) selama 7 hari (10080 menit)
            Cookie::queue('del_last_user_email', $request->email, 10080);

            return redirect()->intended(route('buku.index'))
                ->with('success', 'Autentikasi berhasil! Selamat datang, ' . Auth::user()->name . '. Sesi Anda telah aktif.');
        }

        return back()
            ->withInput($request->only('email', 'remember'))
            ->with('error', 'Kredensial login tidak valid. Periksa kembali email dan kata sandi Anda.');
    }

    /**
     * Memproses logout pengguna, memusnahkan sesi server, dan meregenerasi token CSRF.
     */
    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();

        // Invalidate dan regenerasi token sesi demi keamanan menyeluruh
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')
            ->with('success', 'Sesi server telah dimusnahkan dengan aman. Anda telah berhasil logout.');
    }
}
