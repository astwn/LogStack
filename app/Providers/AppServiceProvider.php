<?php

namespace App\Providers;

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
