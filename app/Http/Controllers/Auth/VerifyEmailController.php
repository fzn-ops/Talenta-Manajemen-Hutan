<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request; 

class VerifyEmailController extends Controller
{
    public function __invoke(Request $request): RedirectResponse
    {
        // Cari user manual berdasarkan ID yang ada di dalam URL link
        $user = User::findOrFail($request->route('id'));

        // Keamanan tambahan: Pastikan hash di URL cocok sama email dia sekarang
        if (! hash_equals((string) $request->route('hash'), sha1($user->getEmailForVerification()))) {
            abort(403, 'Link verifikasi tidak valid atau sudah kedaluwarsa.');
        }

        // Kalau udah pernah verifikasi, langsung lempar ke halaman sukses
        if ($user->hasVerifiedEmail()) {
            abort(403, 'Link sudah kedaluwarsa: Email ini sudah terverifikasi sebelumnya.');
        }

        // Proses verifikasi emailnya
        if ($user->markEmailAsVerified()) {
            event(new Verified($user));
        }

        // Lempar ke halaman sukses
        return redirect('/email/verified-success');
    }
}