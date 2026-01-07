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
    Route::post('/ubicacion/{ubicacionId}/enviar', [\App\Http\Controllers\ReportesPdfController::class, 'enviarUbicacion'])
        ->name('reportes.pdf.ubicacion.enviar');
});

// Excel Reports
Route::middleware('auth')->prefix('reportes-excel')->group(function () {
    Route::get('/responsable/{responsableId}', [\App\Http\Controllers\ReportesExcelController::class, 'responsable'])
        ->name('reportes.excel.responsable');
    Route::post('/responsable/{responsableId}/enviar', [\App\Http\Controllers\ReportesExcelController::class, 'enviarResponsable'])
        ->name('reportes.excel.responsable.enviar');
});

require __DIR__.'/auth.php';

