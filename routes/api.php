<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SuratTugasController;

Route::middleware('auth:sanctum')->prefix('v1')->group(function () {
    Route::get('/surat-tugas', [SuratTugasController::class, 'index']);
    Route::post('/surat-tugas', [SuratTugasController::class, 'store']);
    Route::get('/surat-tugas/{id}', [SuratTugasController::class, 'show']);
    Route::put('/surat-tugas/{id}', [SuratTugasController::class, 'update']);
    Route::delete('/surat-tugas/{id}', [SuratTugasController::class, 'destroy']);
});
