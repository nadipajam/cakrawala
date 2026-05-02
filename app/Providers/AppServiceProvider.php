<?php

namespace App\Providers;

use App\Support\WindowsSafeFilesystem;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton('files', fn () => new WindowsSafeFilesystem);
        $this->app->alias('files', Filesystem::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        JsonResource::withoutWrapping();

        foreach ([
            'airline',
            'airplane',
            'airport',
            'booking',
            'changeRequest',
            'contactMessage',
            'detail',
            'flight',
            'passenger',
            'payment',
            'seat',
            'ticket',
            'user',
            'addon',
        ] as $numericRouteParam) {
            Route::pattern($numericRouteParam, '[0-9]+');
        }
    }
}
