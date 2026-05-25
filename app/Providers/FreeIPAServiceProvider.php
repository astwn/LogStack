<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class FreeIPAServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
	$this->app->singleton(\App\Services\FreeIPAService::class, function ($app) {
            return new \App\Services\FreeIPAService();
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
