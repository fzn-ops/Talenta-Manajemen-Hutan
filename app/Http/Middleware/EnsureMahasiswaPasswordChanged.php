<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Symfony\Component\HttpFoundation\Response;

class EnsureMahasiswaPasswordChanged
{
    /**
     * Handle an incoming request.
     * Mencegah mahasiswa mengakses dashboard & aktivitas jika masih menggunakan password default (NIM) atau belum mengisi email.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && $user->role === 'mahasiswa') {
            $sessionKey = 'auth_is_default_password_' . $user->id;
            if ($request->session()->has($sessionKey)) {
                $isDefaultPassword = $request->session()->get($sessionKey);
            } else {
                $isDefaultPassword = !empty($user->nim) && Hash::check($user->nim, $user->password);
                $request->session()->put($sessionKey, $isDefaultPassword);
            }

            $isEmailEmpty = empty($user->email) || $user->email === '-';
            $isLocked = $isDefaultPassword || $isEmailEmpty;

            if ($isLocked) {
                // Rute yang tetap boleh diakses saat akun belum lengkap/terkunci
                $allowedRouteNames = [
                    'mahasiswa.profile',
                    'mahasiswa.profile.personal',
                    'mahasiswa.profile.account',
                    'mahasiswa.profile.password',
                    'profile.edit',
                    'profile.update',
                    'profile.destroy',
                    'logout',
                ];

                $currentRouteName = $request->route()?->getName();

                if (!in_array($currentRouteName, $allowedRouteNames)) {
                    if ($isDefaultPassword && $isEmailEmpty) {
                        $msg = 'Akun Anda belum lengkap. Silakan lengkapi email aktif dan ganti password default (NIM) Anda untuk membuka akses Dashboard dan Aktivitas.';
                    } elseif ($isEmailEmpty) {
                        $msg = 'Email akun Anda masih kosong. Silakan lengkapi email aktif Anda terlebih dahulu untuk membuka akses Dashboard dan Aktivitas.';
                    } else {
                        $msg = 'Akun Anda masih menggunakan password default (NIM). Silakan ubah password Anda terlebih dahulu untuk membuka akses Dashboard dan Aktivitas.';
                    }

                    return redirect()->route('mahasiswa.profile')->with('warning', $msg);
                }
            }
        }

        return $next($request);
    }
}
