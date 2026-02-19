<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;

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
        Vite::prefetch(concurrency: 3);

        RateLimiter::for('inventario-aprobacion-view', function (Request $request) {
            $token = (string) ($request->route('token') ?? 'na');

            return Limit::perMinute(40)->by("aprobacion:view:{$token}|{$request->ip()}");
        });

        RateLimiter::for('inventario-aprobacion-submit', function (Request $request) {
            $token = (string) ($request->route('token') ?? 'na');

            return Limit::perMinute(10)->by("aprobacion:submit:{$token}|{$request->ip()}");
        });

        RateLimiter::for('firma-entrega-view', function (Request $request) {
            $responsableId = (string) ($request->route('responsable') ?? 'na');

            return Limit::perMinute(40)->by("entrega:view:{$responsableId}|{$request->ip()}");
        });

        RateLimiter::for('firma-entrega-submit', function (Request $request) {
            $responsableId = (string) ($request->route('responsable') ?? 'na');

            return Limit::perMinute(10)->by("entrega:submit:{$responsableId}|{$request->ip()}");
        });

        if (config('app.env') === 'production') {
            URL::forceScheme('https');
        }
    }
}
