<?php

use App\Http\Controllers\PengusulController;
use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\SuratTugasController;
use App\Http\Controllers\Auth\PasswordController;

// Entry
Route::get('/', function () {
    if (Auth::check()) {
        return redirect()->intended(match (Auth::user()->role) {
            'admin' => '/admin/dashboard',
            'pengusul' => '/pengusul/dashboard',
            'wadir1' => '/wadir1/dashboard',
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
    });

    // Wadir1 routes
    Route::prefix('wadir1')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'wadir1'])
            ->name('wadir1.dashboard');
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
