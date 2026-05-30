<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Beritahu Laravel untuk percaya pada Proxy Gateway (101)
        $middleware->trustProxies(at: '*');

        // 🔥 BYPASS CSRF UNTUK CALLBACK DAN DOWNLOAD PROXY
        $middleware->validateCsrfTokens(except: [
            'onlyoffice/callback',
            'document/download-raw'
        ]);

        // DAFTARKAN MIDDLEWARE ROLE DI SINI (Laravel 11 Style)
        $middleware->alias([
            'role' => \App\Http\Middleware\CheckRole::class,
            'authz' => \App\Http\Middleware\CheckPermission::class,
        ]);
    })

    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
