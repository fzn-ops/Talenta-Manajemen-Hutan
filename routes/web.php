<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\Dashboard\Admin\AktivitasController;
use App\Http\Controllers\Dashboard\Mahasiswa\ProfileController as MahasiswaProfileController;
use App\Http\Controllers\Dashboard\Admin\BeritaController;
use App\Http\Controllers\Dashboard\Admin\KarirController;
use App\Http\Controllers\Dashboard\Admin\RoadmapController as AdminRoadmapController;
use App\Http\Controllers\Dashboard\Admin\RoadmapDetailController;
use App\Http\Controllers\Dashboard\Admin\MahasiswaController;
use App\Http\Controllers\Dashboard\Admin\PendaftaranAktivitasController as AdminPendaftaranAktivitasController;
use App\Http\Controllers\Dashboard\Admin\PersetujuanAktivitasController as AdminPersetujuanAktivitasController;
use App\Http\Controllers\Dashboard\Admin\HasilAktivitasController as AdminHasilAktivitasController;
use App\Http\Controllers\Dashboard\Mahasiswa\PengajuanAktivitasController as MahasiswaPengajuanAktivitas;
use App\Http\Controllers\Dashboard\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Dashboard\Mahasiswa\ListAktivitas as MahasiswaListAktivitas;
use App\Http\Controllers\Dashboard\Mahasiswa\RoadmapController as MahasiswaRoadmapController;

/* Route Landing dan Website Public*/

Route::get('/', function () {
    return Inertia::render('landing_page/Home');
});

Route::get('/activities', function () {
    return Inertia::render('landing_page/activity/Activities');
})->name('activities');

/* Route::get('/activities/{id}', function ($id) {
    return Inertia::render('landing_page/activity/ActivityDetail', [
        'activityId' => $id,
    ]);
})->name('activity.detail'); */

Route::get('/activities/{id}', function ($id) {
    return Inertia::render('landing_page/activity/Show');
})->name('activity.detail');

Route::get('/careers', function () {
    return Inertia::render('landing_page/career/Careers');
})->name('careers');

Route::get('/careers/{id}', function ($id) {
    return Inertia::render('landing_page/career/Show');
})->name('career.detail');

Route::get('/news', function () {
    return Inertia::render('landing_page/news/News');
})->name('news');

Route::get('/news/{id}', function ($id) {
    return Inertia::render('landing_page/news/Show');
})->name('news.detail');

Route::get('/FAQ', function () {
    return Inertia::render('landing_page/Faq');
})->name('FAQ');

/*----------------------------------------------------------------------*/

Route::get('/dashboard', function () {
    $user = \Illuminate\Support\Facades\Auth::user();
    if ($user && $user->role === 'mahasiswa') {
        return redirect()->route('mahasiswa.dashboard');
    }
    if ($user && $user->role === 'admin') {
        return redirect('/admin/dashboard');
    }
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified']);

/* Admin Dashboard Routes */
Route::middleware(['auth','role:admin'])->prefix('admin')->group(function(){
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');

    Route::get('/mahasiswa', [MahasiswaController::class, 'index'])->name('mahasiswa');
    Route::post('/mahasiswa/store', [MahasiswaController::class, 'store'])->name('mahasiswa.store');
    Route::put('/mahasiswa/{id}', [MahasiswaController::class, 'update'])->name('mahasiswa.update');
    Route::delete('/mahasiswa/{id}', [MahasiswaController::class, 'destroy'])->name('mahasiswa.destroy');
    Route::post('/mahasiswa/bulk-delete', [MahasiswaController::class, 'bulkDelete'])->name('mahasiswa.bulkDelete');
    Route::post('/mahasiswa/import', [MahasiswaController::class, 'import'])->name('mahasiswa.import');

    Route::get('/roadmap', [AdminRoadmapController::class, 'index'])->name('roadmap.index');
    Route::post('/roadmap', [AdminRoadmapController::class, 'store'])->name('roadmap.store');
    Route::put('/roadmap/{id}', [AdminRoadmapController::class, 'update'])->name('roadmap.update');
    Route::delete('/roadmap/{id}', [AdminRoadmapController::class, 'destroy'])->name('roadmap.destroy');

    // Detail Roadmap, Bulan & Materi
    Route::get('/roadmap/{id}', [RoadmapDetailController::class, 'show'])->name('roadmap.detail');
    Route::post('/roadmap/{roadmapId}/bulan', [RoadmapDetailController::class, 'storeBulan'])->name('roadmap.bulan.store');
    Route::delete('/roadmap/{roadmapId}/bulan/{bulanId}', [RoadmapDetailController::class, 'destroyBulan'])->name('roadmap.bulan.destroy');
    Route::post('/roadmap/{roadmapId}/bulan/{bulanId}/materi', [RoadmapDetailController::class, 'storeMateri'])->name('roadmap.materi.store');
    Route::patch('/roadmap/materi/{materiId}/title', [RoadmapDetailController::class, 'updateMateriTitle'])->name('roadmap.materi.updateTitle');
    Route::put('/roadmap/materi/{materiId}', [RoadmapDetailController::class, 'updateMateriContent'])->name('roadmap.materi.updateContent');
    Route::delete('/roadmap/materi/{materiId}', [RoadmapDetailController::class, 'destroyMateri'])->name('roadmap.materi.destroy');

    Route::prefix('aktivitas')->name('aktivitas.')->group(function () {
        Route::get('/persetujuan', [AdminPersetujuanAktivitasController::class, 'index'])->name('persetujuan');
        Route::post('/persetujuan/{id}/approve', [AdminPersetujuanAktivitasController::class, 'approve'])->name('persetujuan.approve');
        Route::post('/persetujuan/{id}/reject', [AdminPersetujuanAktivitasController::class, 'reject'])->name('persetujuan.reject');
        Route::delete('/persetujuan/{id}', [AdminPersetujuanAktivitasController::class, 'destroy'])->name('persetujuan.destroy');

        Route::get('/bukti-pendaftaran', [AdminPendaftaranAktivitasController::class, 'index'])
            ->name('bukti');

        Route::patch('/bukti-pendaftaran/{id}/approve', [AdminPendaftaranAktivitasController::class, 'approve'])
            ->name('bukti.approve');
            
        Route::patch('/bukti-pendaftaran/{id}/reject', [AdminPendaftaranAktivitasController::class, 'reject'])
            ->name('bukti.reject');

        Route::delete('/bukti-pendaftaran/{id}', [AdminPendaftaranAktivitasController::class, 'destroy'])
            ->name('bukti.destroy');

        Route::get('/hasil', [AdminHasilAktivitasController::class, 'index'])->name('hasil');
        Route::post('/hasil/{id}/approve', [AdminHasilAktivitasController::class, 'approve'])->name('hasil.approve');
        Route::post('/hasil/{id}/reject', [AdminHasilAktivitasController::class, 'reject'])->name('hasil.reject');
        Route::delete('/hasil/{id}', [AdminHasilAktivitasController::class, 'destroy'])->name('hasil.destroy');

        Route::get('/list', [AktivitasController::class, 'index'])->name('listAktivitasAdmin');
        Route::post('/store', [AktivitasController::class, 'store'])->name('storeAktivitasAdmin');
        Route::put('/{id}', [AktivitasController::class, 'update'])->name('updateAktivitasAdmin');
        Route::delete('/{id}', [AktivitasController::class, 'destroy'])->name('destroyAktivitasAdmin');
    });

    Route::get('/karir', [KarirController::class, 'index'])->name('karir');
    Route::post('/karir/store', [KarirController::class, 'store'])->name('karir.store');
    Route::put('/karir/{id}', [KarirController::class, 'update'])->name('karir.update');
    Route::delete('/karir/{id}', [KarirController::class, 'destroy'])->name('karir.destroy');

    Route::prefix('berita')->name('admin.berita.')->group(function(){
        Route::get('/list', [BeritaController::class, 'index'])->name('index');
        Route::post('/store', [BeritaController::class, 'store'])->name('store');
        Route::put('/{id}', [BeritaController::class, 'update'])->name('update'); 
        Route::delete('/{id}', [BeritaController::class, 'destroy'])->name('destroy');
    });

});

/* Mahasiswa Dashboard Routes */
Route::middleware(['auth', 'role:mahasiswa', 'mahasiswa.password'])->prefix('mahasiswa')->name('mahasiswa.')->group(function () {
    Route::get('/dashboard', function () {
        return Inertia::render('dashboard/mahasiswa/Dashboard');
    })->name('dashboard');

    Route::get('/roadmap', [MahasiswaRoadmapController::class, 'index'])->name('roadmap');
    Route::post('/roadmap/enroll', [MahasiswaRoadmapController::class, 'enroll'])->name('roadmap.enroll');
    Route::get('/roadmap/{id}', [MahasiswaRoadmapController::class, 'show'])->name('roadmap.detail');
    Route::post('/roadmap/materi/{materiId}/toggle', [MahasiswaRoadmapController::class, 'toggleMateri'])->name('roadmap.materi.toggle');
    Route::post('/roadmap/materi/{materiId}/hasil', [MahasiswaRoadmapController::class, 'storeHasilAktivitas'])->name('roadmap.materi.hasil');
    Route::delete('/roadmap/hasil/{hasilId}', [MahasiswaRoadmapController::class, 'destroyHasilAktivitas'])->name('roadmap.hasil.destroy');

    Route::prefix('aktivitas')->name('aktivitas.')->group(function () {
        // 1. Halaman List (Nampilin Tab Dibuka & Tab Diikuti sekaligus)
        Route::get('/list', [MahasiswaListAktivitas::class, 'index'])->name('list');
        Route::get('/pendaftaran/{id}', [MahasiswaListAktivitas::class, 'pendaftaran'])->name('pendaftaran');
        Route::post('/pendaftaran/daftar', [MahasiswaListAktivitas::class, 'store'])->name('daftar');
        Route::post('/pendaftaran/{id}/update', [MahasiswaListAktivitas::class, 'update'])->name('update');
        Route::delete('/pendaftaran/{id}/batal', [MahasiswaListAktivitas::class, 'destroy'])->name('batal');

        Route::prefix('pengajuan')->name('pengajuan.')->group(function () {
            Route::get('/', [MahasiswaPengajuanAktivitas::class, 'index'])->name('index');
            Route::post('/', [MahasiswaPengajuanAktivitas::class, 'store'])->name('store');
            Route::post('/{id}', [MahasiswaPengajuanAktivitas::class, 'update'])->name('update');
            Route::delete('/{id}', [MahasiswaPengajuanAktivitas::class, 'destroy'])->name('destroy');
        });
    });

    Route::get('/profile', [MahasiswaProfileController::class, 'index'])->name('profile');
    Route::post('/profile/account', [MahasiswaProfileController::class, 'updateAccount'])->name('profile.account');
    Route::post('/profile/password', [MahasiswaProfileController::class, 'updatePassword'])->name('profile.password');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
