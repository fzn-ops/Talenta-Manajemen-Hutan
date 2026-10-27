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

        // 1. Deteksi perubahan email
        $emailBerubah = $user->email !== $validated['email'];

        // 2. Update data biasa (yang diizinkan di $fillable)
        $user->update([
            'email' => $validated['email'],
            'no_handphone' => $validated['no_handphone'] ?? null,
        ]);

        if ($emailBerubah) {
            $user->email_verified_at = null; 
            $user->save(); 

            $user->sendEmailVerificationNotification();
            
            return back()->with('success', 'Data akun diperbarui. Silakan cek kotak masuk Anda untuk verifikasi email baru!');
        }

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

    /**
     * Perbarui foto profil mahasiswa.
     */
    public function updatePhoto(Request $request): RedirectResponse
    {
        $request->validate([
            'foto' => ['required', 'image', 'mimes:jpeg,png,jpg', 'max:10240'],
        ], [
            'foto.required' => 'File foto profil wajib diunggah.',
            'foto.image' => 'File harus berupa gambar.',
            'foto.mimes' => 'Format foto harus berupa JPG, PNG, atau JPEG.',
            'foto.max' => 'Ukuran foto maksimal adalah 10MB.',
        ]);

        $user = $request->user();

        // Hapus foto lama jika ada di disk public
        if (!empty($user->foto) && !str_starts_with($user->foto, 'http://') && !str_starts_with($user->foto, 'https://')) {
            $cleanOld = ltrim(str_replace('storage/', '', $user->foto), '/');
            if (\Illuminate\Support\Facades\Storage::disk('public')->exists($cleanOld)) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($cleanOld);
            }
        }

        $file = $request->file('foto');
        $filename = 'profile_' . $user->id . '_' . time() . '.' . $file->getClientOriginalExtension();
        $path = $file->storeAs('profile_pictures', $filename, 'public');

        $user->update([
            'foto' => 'storage/' . $path,
        ]);

        return back()->with('success', 'Foto profil berhasil diperbarui!');
    }
}
