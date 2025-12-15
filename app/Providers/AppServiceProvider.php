<?php

namespace App\Providers;

use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;
use Inertia\Inertia;
use Illuminate\Support\Facades\Cache;
use App\Models\TemplateSurat;

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
        Inertia::share('activeTemplateSurat', function () {
            return Cache::remember('active_template_surat', 60, function () {
                return TemplateSurat::query()
                    ->where('status', 1)
                    ->orderByDesc('id')
                    ->first();
            });
        });
    }
}
