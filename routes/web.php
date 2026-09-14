<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('landing_page/Home');
});

Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

/* Admin Dashboard Routes */
Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', function () {
        return Inertia::render('dashboard/admin/Dashboard');
    })->name('dashboard');

    Route::get('/mahasiswa', function () {
        return Inertia::render('dashboard/admin/Dashboard');
    })->name('mahasiswa');

    Route::get('/roadmap', function () {
        return Inertia::render('dashboard/admin/Dashboard');
    })->name('roadmap');

    Route::prefix('aktivitas')->name('aktivitas.')->group(function () {
        Route::get('/persetujuan', function () {
            return Inertia::render('dashboard/admin/Dashboard');
        })->name('persetujuan');

        Route::get('/bukti-pendaftaran', function () {
            return Inertia::render('dashboard/admin/Dashboard');
        })->name('bukti');

        Route::get('/hasil', function () {
            return Inertia::render('dashboard/admin/Dashboard');
        })->name('hasil');

        Route::get('/list', function () {
            return Inertia::render('dashboard/admin/Dashboard');
        })->name('list');
    });

    Route::get('/karir', function () {
        return Inertia::render('dashboard/admin/Dashboard');
    })->name('karir');

    Route::get('/berita', function () {
        return Inertia::render('dashboard/admin/Dashboard');
    })->name('berita');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
