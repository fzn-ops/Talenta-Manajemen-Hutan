<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that is loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determine the current asset version.
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $user = $request->user();
        $isDefaultPassword = false;
        $isEmailEmpty = false;
        $isLocked = false;

        if ($user && $user->role === 'mahasiswa') {
            $sessionKey = 'auth_is_default_password_' . $user->id;
            if ($request->session()->has($sessionKey)) {
                $isDefaultPassword = (bool) $request->session()->get($sessionKey);
            } else {
                $isDefaultPassword = !empty($user->nim) && \Illuminate\Support\Facades\Hash::check($user->nim, $user->password);
                $request->session()->put($sessionKey, $isDefaultPassword);
            }

            $isEmailEmpty = empty($user->email) || $user->email === '-';
            $isPhoneEmpty = empty($user->no_handphone) || $user->no_handphone === '-';
            $isLocked = $isDefaultPassword || $isEmailEmpty || $isPhoneEmpty;
        }

        return [
            ...parent::share($request),
            'auth' => [
                'user' => $user ? array_merge($user->toArray(), [
                    'is_default_password' => $isDefaultPassword,
                    'is_email_empty' => $isEmailEmpty,
                    'is_phone_empty' => $isPhoneEmpty ?? false,
                    'is_locked' => $isLocked,
                ]) : null,
            ],
            'flash' => [
                'success' => fn () => $request->session()->get('success'),
                'error' => fn () => $request->session()->get('error'),
                'warning' => fn () => $request->session()->get('warning'),
            ],
        ];
    }
}
