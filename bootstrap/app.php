<?php

use App\Http\Middleware\CheckRole;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Symfony\Component\HttpKernel\Exception\HttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->web(append: [
            \App\Http\Middleware\HandleInertiaRequests::class,
            \Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets::class,
        ]);

        $middleware->redirectUsersTo(function(Request $request){
            $user=Auth::user();
            if($user && $user->role == 'admin'){
                return '/admin/dashboard';
            }
            if($user && $user->role == 'mahasiswa'){
                $isDefault = !empty($user->nim) && \Illuminate\Support\Facades\Hash::check($user->nim, $user->password);
                $isEmailEmpty = empty($user->email) || $user->email === '-';
                if ($isDefault || $isEmailEmpty) {
                    return '/mahasiswa/profile';
                }
                return '/mahasiswa/dashboard';
            }
            return '/';
        });

        $middleware->alias([
            'role' => CheckRole::class,
            'mahasiswa.password' => \App\Http\Middleware\EnsureMahasiswaPasswordChanged::class,
        ]);

        //
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );
        $exceptions->render(function (HttpException $e, $request) {
            if (in_array($e->getStatusCode(), [403, 404, 500, 503])) {
                return Inertia::render('Errors/' . $e->getStatusCode(), [
                    'status' => $e->getStatusCode(),
                    'message' => $e->getMessage(),
                ])
                ->toResponse($request)
                ->setStatusCode($e->getStatusCode());
            }
        });
    })->create();