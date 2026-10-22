<?php

namespace App\Providers;

use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Notifications\Messages\MailMessage;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Biarkan bawaan Laravel 11 ini tetap ada
        Vite::prefetch(concurrency: 3);

        // 🔥 2. Tambahkan pengaturan kustomisasi template email di sini
        VerifyEmail::toMailUsing(function (object $notifiable, string $url) {
            return (new MailMessage)
                ->subject('Aksi Diperlukan: Verifikasi Email Akun Talenta')
                ->view('emails.custom_verify_email', [
                    'url' => $url,
                    'nama' => $notifiable->name,
                ]);
        });
    }
}