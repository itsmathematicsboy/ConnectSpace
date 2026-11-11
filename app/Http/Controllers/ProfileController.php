<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Tampilkan halaman edit profil.
     */
    public function edit(): View
    {
        return view('profile.edit', ['user' => Auth::user()]);
    }

    /**
     * Update profil (name, bio, avatar).
     */
    public function update(Request $request): RedirectResponse
    {
        /** @var User $user */
        $user = Auth::user();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100', 'regex:/^[\p{L}\s.\-]+$/u'],
            'bio' => ['nullable', 'string', 'max:500'],
            'avatar' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp,gif', 'max:2048'],
        ], [
            'name.required' => 'Nama wajib diisi.',
            'name.regex' => 'Nama hanya boleh berisi huruf, spasi, titik, dan tanda hubung.',
            'bio.max' => 'Bio maksimal 500 karakter.',
            'avatar.image' => 'File harus berupa gambar.',
            'avatar.mimes' => 'Format gambar harus JPG, JPEG, PNG, WEBP, atau GIF.',
            'avatar.max' => 'Ukuran gambar maksimal 2MB.',
        ]);

        $user->name = $validated['name'];
        $user->bio = $validated['bio'] ?: null;

        // Handle upload avatar
        if ($request->hasFile('avatar') && $request->file('avatar')->isValid()) {
            // Hapus avatar lama jika disimpan lokal
            if ($user->avatar && ! filter_var($user->avatar, FILTER_VALIDATE_URL)) {
                $oldPath = storage_path('app/public/'.$user->avatar);
                if (file_exists($oldPath)) {
                    @unlink($oldPath);
                }
            }
            $path = $request->file('avatar')->store('avatars', 'public');
            $user->avatar = $path;
        }

        $user->save();

        return back()->with('success', 'Profil berhasil diperbarui.');
    }

    /**
     * Tampilkan halaman change password.
     */
    public function editPassword(): View
    {
        return view('profile.password', ['user' => Auth::user()]);
    }

    /**
     * Update password.
     */
    public function updatePassword(Request $request): RedirectResponse
    {
        /** @var User $user */
        $user = Auth::user();

        $validated = $request->validate([
            'current_password' => ['required', 'string'],
            'password' => ['required', 'string', 'confirmed', Password::min(6)],
        ], [
            'current_password.required' => 'Password lama wajib diisi.',
            'password.required' => 'Password baru wajib diisi.',
            'password.confirmed' => 'Konfirmasi password tidak cocok.',
            'password.min' => 'Password baru minimal 6 karakter.',
        ]);

        // Verifikasi password lama
        if (! Hash::check($validated['current_password'], $user->password)) {
            return back()->withErrors(['current_password' => 'Password lama tidak sesuai.'])->withInput();
        }

        // Cegah password baru sama dengan lama
        if (Hash::check($validated['password'], $user->password)) {
            return back()->withErrors(['password' => 'Password baru tidak boleh sama dengan password lama.'])->withInput();
        }

        $user->password = Hash::make($validated['password']);
        $user->save();

        return back()->with('success', 'Password berhasil diperbarui. Silakan login dengan password baru pada kunjungan berikutnya.');
    }
}
