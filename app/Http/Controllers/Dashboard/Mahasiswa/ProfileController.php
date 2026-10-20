<?php

namespace App\Http\Controllers\Dashboard\Mahasiswa;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class ProfileController extends Controller
{
    /**
     * Tampilkan halaman profil mahasiswa.
     */
    public function index(Request $request): Response
    {
        $user = $request->user();

        $sessionKey = 'auth_is_default_password_' . $user->id;
        if ($request->session()->has($sessionKey)) {
            $isDefaultPassword = (bool) $request->session()->get($sessionKey);
        } else {
            $isDefaultPassword = !empty($user->nim) && Hash::check($user->nim, $user->password);
            $request->session()->put($sessionKey, $isDefaultPassword);
        }

        $isEmailEmpty = empty($user->email) || $user->email === '-';
        $isPhoneEmpty = empty($user->no_handphone) || $user->no_handphone === '-';
        $isLocked = $isDefaultPassword || $isEmailEmpty;

        return Inertia::render('dashboard/mahasiswa/Profile', [
            'userData' => $user,
            'isDefaultPassword' => $isDefaultPassword,
            'isEmailEmpty' => $isEmailEmpty,
            'isPhoneEmpty' => $isPhoneEmpty,
            'isLocked' => $isLocked,
        ]);
    }

    /**
     * Perbarui data akun (email & nomor handphone).
     */
    public function updateAccount(Request $request): RedirectResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($user->id),
            ],
            'no_handphone' => ['nullable', 'string', 'max:20'],
        ], [
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid (contoh: nama@email.com).',
            'email.unique' => 'Email ini sudah digunakan oleh akun lain.',
        ]);

        $user->update([
            'email' => $validated['email'],
            'no_handphone' => $validated['no_handphone'] ?? null,
        ]);

        return back()->with('success', 'Data akun berhasil diperbarui.');
    }

    /**
     * Perbarui password mahasiswa.
     */
    public function updatePassword(Request $request): RedirectResponse
    {
        $user = $request->user();

        $request->validate([
            'current_password' => ['required', 'string'],
            'new_password' => ['required', 'string', 'min:8', 'confirmed'],
        ], [
            'current_password.required' => 'Password saat ini wajib diisi.',
            'new_password.required' => 'Password baru wajib diisi.',
            'new_password.min' => 'Password baru minimal 8 karakter.',
            'new_password.confirmed' => 'Konfirmasi password baru tidak cocok.',
        ]);

        if (!Hash::check($request->current_password, $user->password)) {
            return back()->withErrors(['current_password' => 'Password saat ini tidak sesuai.']);
        }

        if (!empty($user->nim) && $request->new_password === $user->nim) {
            return back()->withErrors(['new_password' => 'Password baru tidak boleh sama dengan NIM Anda.']);
        }

        $user->update([
            'password' => Hash::make($request->new_password),
        ]);

        // Reset status password default di session
        $sessionKey = 'auth_is_default_password_' . $user->id;
        $request->session()->put($sessionKey, false);

        return back()->with('success', 'Password berhasil diubah!');
    }
}
