<?php

use App\Http\Controllers\PengusulController;
use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\SuratTugasController;
use App\Http\Controllers\PelaksanaController;
use App\Http\Controllers\Direktur\DaftarPersetujuanController;
use App\Http\Controllers\BKU\DaftarLaporanController;
use App\Http\Controllers\Auth\PasswordController;
use App\Http\Controllers\Wadir\WadirController;


use App\Http\Controllers\SekdirController;

// To force logout in case of accidental role change
// use Illuminate\Support\Facades\Auth;

// Route::get('/force-logout', function () {
//     Auth::logout();
//     session()->invalidate();
//     session()->regenerateToken();

//     return redirect('/login')->with('status', 'You have been logged out.');
// });

// Entry
Route::get('/', function () {
    if (Auth::check()) {
        return redirect()->intended(match (Auth::user()->role) {
            'admin' => '/admin/dashboard',
            'pengusul' => '/pengusul/dashboard',
            'wadir1' => '/wadir1/dashboard',
            'wadir2' => '/wadir2/dashboard',
            'wadir3' => '/wadir3/dashboard',
            'wadir4' => '/wadir4/dashboard',
            'direktur' => '/direktur/dashboard',
            'pelaksana' => '/pelaksana/dashboard',
            'bku' => '/bku/dashboard',
            'sekdir'=>'/sekdir/dashboard',
            default => '/login',
        });
    }

    return redirect('/login');
});

Route::middleware(['auth'])->group(function () {
    // Admin
    Route::prefix('admin')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'admin'])
            ->name('admin.dashboard');
    });

    // Pengusul 
    Route::prefix('pengusul')->middleware(['auth', 'role:pengusul'])->group(function () {
        Route::get('/dashboard', [PengusulController::class, 'dashboard'])->name('pengusul.dashboard');
        Route::get('/pengusulan', [PengusulController::class, 'daftarPengusulan'])->name('pengusul.pengajuan');
        Route::get('/tambah-pengusulan', [PengusulController::class, 'formPengusulan'])->name('pengusul.form');
    });

    // Pelaksana
    Route::prefix('pelaksana')->name('pelaksana.')->middleware(['auth', 'role:pelaksana'])->group(function () {
        Route::get('/dashboard', [PelaksanaController::class, 'dashboard'])->name('dashboard');
        Route::get('/daftarlaporan', [PelaksanaController::class, 'daftarLaporan'])->name('daftarlaporan');
    });

    Route::middleware(['auth'])->group(function () {

        $wadirList = ['wadir1', 'wadir2', 'wadir3', 'wadir4'];

        foreach ($wadirList as $wadir) {

            Route::prefix($wadir)
                ->name($wadir . '.')
                ->middleware(['role:' . $wadir])
                ->group(function () {

                    // Dashboard
                    Route::get('/dashboard', [WadirController::class, 'index'])
                        ->name('dashboard');

                    // Halaman daftar persetujuan
                    Route::get('/persetujuan', [WadirController::class, 'persetujuan'])
                        ->name('persetujuan');

                    // Lihat surat untuk disetujui
                    Route::get('/persetujuan/{id}', [WadirController::class, 'show'])
                        ->name('persetujuan.show');

                    // Aksi approve
                    Route::post('/persetujuan/{id}/approve', [WadirController::class, 'approve'])
                        ->name('persetujuan.approve');

                    // Aksi reject
                    Route::post('/persetujuan/{id}/reject', [WadirController::class, 'reject'])
                        ->name('persetujuan.reject');
                });
        }
    });


    // Direktur routes
    Route::prefix('direktur')->name('direktur.')->middleware(['auth', 'role:direktur'])->group(function () {

        // Dashboard Direktur
        Route::get('/dashboard', [DashboardController::class, 'direktur'])
            ->name('dashboard');

        // Daftar Persetujuan Page
        Route::get('/daftarpersetujuan', [DaftarPersetujuanController::class, 'index'])
            ->name('daftarpersetujuan');

    });

    // BKU routes
    Route::prefix('bku')->name('bku.')->middleware(['auth', 'role:bku'])->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'bku'])
            ->name('dashboard');

        // Daftar Laporan & Perjalanan
        Route::get('/daftarlaporan&perjalanan', [DaftarLaporanController::class, 'index'])
            ->name('daftarlaporan&perjalanan');
    });

    // SEKDIR
    Route::prefix('sekdir')->name('sekdir.')->middleware(['auth', 'role:sekdir'])->group(function () {
        Route::get('/dashboard', [SekdirController::class, 'dashboard'])->name('dashboard');
        Route::get('/nomor-surat', [SekdirController::class, 'nomorSurat'])->name('nomorsurat');
        Route::get('/nomor-surat/{id}/review', [SekdirController::class, 'review'])->name('nomorsurat.review');
        Route::post('/nomor-surat/{id}/apply', [SekdirController::class, 'applyNomor'])->name('nomorsurat.apply');
        Route::get('/history', [SekdirController::class, 'history'])->name('history');
    });

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Surat Tugas
// Route::middleware(['auth'])->group(function () {
//     Route::resource('surat-tugas', SuratTugasController::class)->only(['index', 'show']);
// });

Route::prefix('surat-tugas')->group(function () {
    Route::get('/', [SuratTugasController::class, 'index']);
    Route::get('/{id}', [SuratTugasController::class, 'show']);
    Route::post('/', [SuratTugasController::class, 'store']);
    Route::put('/{id}', [SuratTugasController::class, 'update']);
    Route::delete('/{id}', [SuratTugasController::class, 'destroy']);
});

require __DIR__.'/auth.php';