<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// PDF Reports (requires auth)
Route::middleware('auth')->prefix('reportes-pdf')->group(function () {
    Route::get('/ubicacion/{ubicacionId}', [\App\Http\Controllers\ReportesPdfController::class, 'ubicacion'])
        ->name('reportes.pdf.ubicacion');
    Route::get('/responsable/{responsableId}', [\App\Http\Controllers\ReportesPdfController::class, 'responsable'])
        ->name('reportes.pdf.responsable');
});

require __DIR__.'/auth.php';
