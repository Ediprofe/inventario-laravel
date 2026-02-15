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
    Route::get('/responsable/{responsableId}', [\App\Http\Controllers\ReportesPdfController::class, 'responsable'])
        ->name('reportes.pdf.responsable');
    Route::post('/responsable/{responsableId}/enviar', [\App\Http\Controllers\ReportesPdfController::class, 'enviarResponsable'])
        ->name('reportes.pdf.responsable.enviar');
});

// Excel Reports
Route::middleware('auth')->prefix('reportes-excel')->group(function () {
    Route::get('/ubicacion/{ubicacionId}', [\App\Http\Controllers\ReportesExcelController::class, 'ubicacion'])
        ->name('reportes.excel.ubicacion');
    Route::get('/responsable/{responsableId}', [\App\Http\Controllers\ReportesExcelController::class, 'responsable'])
        ->name('reportes.excel.responsable');
    Route::post('/responsable/{responsableId}/enviar', [\App\Http\Controllers\ReportesExcelController::class, 'enviarResponsable'])
        ->name('reportes.excel.responsable.enviar');
});

// Public inventory approval (no auth required)
Route::get('/inventario/aprobar/{token}', [\App\Http\Controllers\AprobacionInventarioController::class, 'mostrar'])
    ->name('inventario.aprobar');
Route::post('/inventario/aprobar/{token}', [\App\Http\Controllers\AprobacionInventarioController::class, 'confirmar'])
    ->name('inventario.aprobar.confirmar');

// Public capture page for "firma de entrega" (signed URL)
Route::get('/firma-entrega/capturar/{responsable}', [\App\Http\Controllers\FirmaEntregaController::class, 'mostrar'])
    ->name('firma.entrega.capturar')
    ->middleware('signed:relative');
Route::post('/firma-entrega/capturar/{responsable}', [\App\Http\Controllers\FirmaEntregaController::class, 'guardar'])
    ->name('firma.entrega.guardar')
    ->middleware('signed:relative');

require __DIR__.'/auth.php';
