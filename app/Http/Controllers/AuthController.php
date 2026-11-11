<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class AuthController extends Controller
{
    /**
     * Tampilkan halaman register (hanya untuk tamu).
     */
    public function showRegister(): View|RedirectResponse
    {
        return view('auth.register');
    }

    /**
     * Proses registrasi + auto-login.
     */
    public function register(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100', 'regex:/^[\p{L}\s.\-]+$/u'],
            'email' => ['required', 'string', 'email:rfc', 'max:150', 'unique:users,email'],
            'password' => ['required', 'string', 'confirmed', Password::min(6)],
        ], [
            'name.required' => 'Nama lengkap wajib diisi.',
            'name.regex' => 'Nama hanya boleh berisi huruf, spasi, titik, dan tanda hubung.',
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'email.unique' => 'Email sudah terdaftar. Silakan login.',
            'password.required' => 'Password wajib diisi.',
            'password.confirmed' => 'Password dan konfirmasi password tidak cocok.',
            'password.min' => 'Password minimal terdiri dari 6 karakter.',
        ]);

        // Hash password menggunakan bcrypt (default Hash facade)
        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']), // bcrypt
        ]);

        // Auto-login setelah registrasi
        Auth::login($user, true);
        $request->session()->regenerate();

        return redirect()->route('home')->with('success', 'Selamat datang di Friendster, '.$user->name.'! Akunmu berhasil dibuat.');
    }

    /**
     * Tampilkan halaman login (hanya untuk tamu).
     */
    public function showLogin(): View|RedirectResponse
    {
        return view('auth.login');
    }

    /**
     * Proses login.
     */
    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'string', 'email:rfc'],
            'password' => ['required', 'string'],
        ], [
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'password.required' => 'Password wajib diisi.',
        ]);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();

            return redirect()->intended(route('home'))
                ->with('success', 'Halo, '.Auth::user()->name.'! Kamu berhasil masuk.');
        }

        return back()
            ->withInput($request->only('email'))
            ->with('error', 'Email atau password salah. Silakan coba lagi.');
    }

    /**
     * Logout + hapus sesi.
     */
    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('success', 'Kamu berhasil keluar. Sampai jumpa!');
    }

}
