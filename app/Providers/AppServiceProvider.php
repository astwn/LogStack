<?php

namespace App\Providers;

use App\Services\AuthorizationCenterClient;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;
use Laravel\Socialite\Facades\Socialite;

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
        Blade::if('authz', function (string $permission) {
            return AuthorizationCenterClient::can($permission);
        });

        Blade::if('authzAny', function (...$permissions) {
            return AuthorizationCenterClient::any($permissions);
        });

        Blade::if('authzAll', function (...$permissions) {
            return AuthorizationCenterClient::all($permissions);
        });

        // 1. PAKSA HTTPS
        // Penting agar redirect_uri yang dikirim ke Keycloak selalu diawali https://
        if (config('app.env') !== 'local') {
            URL::forceScheme('https');
        }

        // 2. EXTEND KEYCLOAK
        // Memastikan Socialite menggunakan driver Keycloak dengan config dari services.php
        Socialite::extend('keycloak', function ($app) {
            $config = $app['config']['services.keycloak'];

            return Socialite::buildProvider(
                \SocialiteProviders\Keycloak\Provider::class,
                $config
            );
        });
    }
}
