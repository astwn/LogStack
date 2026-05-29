<?php

/**
 * LogStack App Branding Configuration
 * 
 * Konfigurasi ini bisa di-override oleh branding.json di storage/app/
 * Edit via Admin Dashboard → Branding Settings
 */

return [
    'app_name'        => env('BRAND_APP_NAME', 'LogStack'),
    'app_full_name'   => env('BRAND_APP_FULL_NAME', 'LogStack Central'),
    'app_tagline'     => env('BRAND_APP_TAGLINE', 'Pusat kendali infrastruktur digital terintegrasi untuk kedaulatan data dan efisiensi manajemen node korporasi.'),
    'app_logo_icon'   => env('BRAND_LOGO_ICON', 'fa-layer-group'),
    'app_version'     => env('BRAND_APP_VERSION', 'v1.0.0'),
    'footer_text'     => env('BRAND_FOOTER_TEXT', 'LogStack Central • Project Sovereign'),
    'primary_color'   => env('BRAND_PRIMARY_COLOR', '#2563eb'),
    'accent_color'    => env('BRAND_ACCENT_COLOR', '#7c3aed'),
    'loader_text'     => env('BRAND_LOADER_TEXT', 'LogStack'),
];
