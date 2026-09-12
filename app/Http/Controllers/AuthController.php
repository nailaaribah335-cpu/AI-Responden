<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

/**
 * Menangani Register, Login, dan Logout untuk Seller.
 */
class AuthController extends Controller
{
    // ─── Register ──────────────────────────────────────────────────

    public function showRegister()
    {
        return view('auth.register');
    }

    public function register(Request $request)
    {
        // Validasi input dari form register
        $data = $request->validate([
            'name'       => 'required|string|max:100',
            'store_name' => 'required|string|max:100',
            'email'      => 'required|email|unique:users',
            'password'   => 'required|min:8|confirmed', // confirmed = harus ada password_confirmation
        ]);

        // Buat akun seller baru
        $user = User::create([
            ...$data,
            'password' => Hash::make($data['password']),
        ]);

        // Langsung login setelah register
        Auth::login($user);

        return redirect()->route('dashboard')->with('success', 'Selamat datang, ' . $user->name . '!');
    }

    // ─── Login ─────────────────────────────────────────────────────

    public function showLogin()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email'    => 'required|email',
            'password' => 'required',
        ]);

        // Coba autentikasi, jika berhasil redirect ke dashboard
        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate(); // Cegah session fixation attack
            return redirect()->intended(route('dashboard'));
        }

        // Jika gagal, kembalikan ke form login dengan pesan error
        return back()->withErrors([
            'email' => 'Email atau password salah.',
        ])->onlyInput('email');
    }

    // ─── Logout ────────────────────────────────────────────────────

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
