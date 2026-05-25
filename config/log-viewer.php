<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Log Viewer Route Path
    |--------------------------------------------------------------------------
    | Ini adalah URL utama untuk mengakses halaman dashboard log viewer Anda.
    */
    'route_path' => 'log-viewer',

    /*
    |--------------------------------------------------------------------------
    | Log Viewer Route Middleware
    |--------------------------------------------------------------------------
    | Di sini tempat kita mengunci halamannya. Kita masukkan middleware 'auth'
    | dan custom middleware 'role:admin' milik kita ke dalam array ini.
    */
    'middleware' => [
        'web',
        'auth',
        'role:admin', // <--- GEMBOK KITA SUDAH STANDBY DI SINI, BANG!
    ],

    /*
    |--------------------------------------------------------------------------
    | Include & Exclude Log Files
    |--------------------------------------------------------------------------
    */
    'include_files' => [
        '*.log',                               // Membaca log Laravel bawaan
        '/var/log/nginx/access.log',           // Tambah Log Akses Nginx
        '/var/log/nginx/error.log',            // Tambah Log Error Nginx
    ],

    'exclude_files' => [],

    /*
    |--------------------------------------------------------------------------
    | Shorthand Notation for Log Levels
    |--------------------------------------------------------------------------
    */
    'patterns' => [
        'laravel' => [
            'log_text_pattern' => '/^\[(?<date>.*)\]\s(?<env>\w+)\.(?<level>\w+):(?<message>.*)/m',
        ],
    ],
];
