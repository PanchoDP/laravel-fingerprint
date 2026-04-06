<?php

declare(strict_types=1);

namespace Panchodp\LaravelFingerprint;

use Illuminate\Foundation\Http\Kernel;
use Illuminate\Routing\Router;
use Illuminate\Support\ServiceProvider;
use Panchodp\LaravelFingerprint\Http\Middleware\FingerprintGuard;

final class LaravelFingerprintServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/laravel_fingerprint.php',
            'laravel_fingerprint'
        );

        $this->app->bind(Fingerprint::class);
    }

    public function boot(Router $router): void
    {
        $router->aliasMiddleware('fingerprint', FingerprintGuard::class);

        if (config('laravel_fingerprint.global', false)) {
            $this->app->make(Kernel::class)->pushMiddleware(FingerprintGuard::class);
        }

        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/laravel_fingerprint.php' => config_path('laravel_fingerprint.php'),
            ], 'laravel-fingerprint-config');
        }
    }
}
