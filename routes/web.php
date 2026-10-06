<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

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
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

/* Admin Dashboard Routes */
Route::middleware(['auth','role:admin'])->prefix('admin')->group(function(){
    Route::get('/dashboard', function () {
        return Inertia::render('dashboard/admin/Dashboard');
    })->name('dashboard');

    Route::get('/mahasiswa', function () {
        return Inertia::render('dashboard/admin/Mahasiswa');
    })->name('mahasiswa');

    Route::get('/roadmap', function () {
        return Inertia::render('dashboard/admin/Roadmap');
    })->name('roadmap');

    Route::get('/roadmap/{id}', function ($id = 1) {
        return Inertia::render('dashboard/admin/RoadmapDetail', [
            'roadmapId' => $id,
        ]);
    })->name('roadmap.detail');

    Route::prefix('aktivitas')->name('aktivitas.')->group(function () {
        Route::get('/persetujuan', function () {
            return Inertia::render('dashboard/admin/ActivityApproval');
        })->name('persetujuan');

        Route::get('/bukti-pendaftaran', function () {
            return Inertia::render('dashboard/admin/ActivityRegister');
        })->name('bukti');

        Route::get('/hasil', function () {
            return Inertia::render('dashboard/admin/ActivityResult');
        })->name('hasil');

        Route::get('/list', function () {
            return Inertia::render('dashboard/admin/ActivityList');
        })->name('list');
    });

    Route::get('/karir', function () {
        return Inertia::render('dashboard/admin/CareerList');
    })->name('karir');

    Route::get('/berita', function () {
        return Inertia::render('dashboard/admin/NewsList');
    })->name('berita');
});

/* Mahasiswa Dashboard Routes */
Route::middleware(['auth','role:mahasiswa'])->prefix('mahasiswa')->name('mahasiswa.')->group(function () {
    Route::get('/dashboard', function () {
        return Inertia::render('dashboard/mahasiswa/Dashboard');
    })->name('dashboard');

    Route::get('/roadmap', function () {
        return Inertia::render('dashboard/mahasiswa/Roadmap');
    })->name('roadmap');

    Route::get('/roadmap/{id}', function ($id = 1) {
        return Inertia::render('dashboard/mahasiswa/RoadmapDetail', [
            'roadmapId' => $id,
        ]);
    })->name('roadmap.detail');

    Route::prefix('aktivitas')->name('aktivitas.')->group(function () {
        Route::get('/list', function () {
            return Inertia::render('dashboard/mahasiswa/ActivityList');
        })->name('list');

        Route::get('/pendaftaran', function () {
            return Inertia::render('dashboard/mahasiswa/ActivityRegister');
        })->name('pendaftaran');

        Route::get('/pengajuan', function () {
            return Inertia::render('dashboard/mahasiswa/ActivitySubmission');
        })->name('pengajuan');
    });

    Route::get('/profile', function () {
        return Inertia::render('dashboard/mahasiswa/Profile');
    })->name('profile');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
