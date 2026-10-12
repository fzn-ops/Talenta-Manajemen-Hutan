<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Inertia\Response;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): Response
    {
        return Inertia::render('Auth/Login', [
            'canResetPassword' => Route::has('password.request'),
            'status' => session('status'),
        ]);
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        $user = request()->user();

        if ($user->role === 'admin') {
            return redirect('/admin/dashboard');
        } elseif ($user->role === 'mahasiswa') {
            $sessionKey = 'auth_is_default_password_' . $user->id;
            if ($request->session()->has($sessionKey)) {
                $isDefaultPassword = (bool) $request->session()->get($sessionKey);
            } else {
                $isDefaultPassword = !empty($user->nim) && \Illuminate\Support\Facades\Hash::check($user->nim, $user->password);
                $request->session()->put($sessionKey, $isDefaultPassword);
            }

            $isEmailEmpty = empty($user->email) || $user->email === '-';
            $isLocked = $isDefaultPassword || $isEmailEmpty;

            if ($isLocked) {
                return redirect()->route('mahasiswa.profile');
            }

            return redirect('/mahasiswa/dashboard');
        }
        return redirect('/login');
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/login');
    }
}
