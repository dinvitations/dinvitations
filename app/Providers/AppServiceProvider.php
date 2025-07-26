<?php

namespace App\Providers;

use Filament\Support\Assets\Js;
use Filament\Support\Facades\FilamentAsset;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        if ($this->app->environment('local') && class_exists(\Laravel\Telescope\TelescopeServiceProvider::class)) {
            $this->app->register(\Laravel\Telescope\TelescopeServiceProvider::class);
            $this->app->register(TelescopeServiceProvider::class);
        }
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        require_once app_path('Support/Helpers.php');

        FilamentAsset::register([
            Js::make('html5-qrcode', 'https://unpkg.com/html5-qrcode'),
            Js::make('webcam', 'https://unpkg.com/webcam-easy/dist/webcam-easy.min.js'),
            Js::make('qrScanner', __DIR__ . '/../../resources/js/qrScanner.js'),
            Js::make('qrPrinter',  __DIR__ . '/../../resources/js/qrPrinter.js'),
            Js::make('selfieStation',  __DIR__ . '/../../resources/js/selfieStation.js'),
        ]);
    }
}
