<?php

return [
    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'keycloak' => [
        'client_id'     => env('KEYCLOAK_CLIENT_ID'),
        'client_secret' => env('KEYCLOAK_CLIENT_SECRET'),
        'redirect'      => env('KEYCLOAK_REDIRECT_URI'),
        'base_url'      => env('KEYCLOAK_BASE_URL'),
        'realms'        => env('KEYCLOAK_REALM'), // Pakai 'realms' sesuai kebutuhan vendor
    ],

    'authorization_center' => [
        'url' => rtrim(env('AUTHORIZATION_CENTER_URL', env('AUTHORIZATION_SERVER_URL', 'http://localhost:8080')), '/'),
        'app_code' => env('AUTHORIZATION_CENTER_APP_CODE', env('AUTHORIZATION_APP_CODE', 'logstack-app')),
    ],

   'freeipa' => [
        'url' => env('FREEIPA_URL', 'https://ipa.logstack.web.id'),
        'user' => env('FREEIPA_ADMIN_USER', 'admin'),
        'password' => env('FREEIPA_ADMIN_PASSWORD'),
    ],

   'odoo' => [
        'url'      => env('ODOO_URL'),
        'db'       => env('ODOO_DB', 'odoo'),
        'username' => env('ODOO_SVC_USER'),
        'password' => env('ODOO_SVC_PASS'),
    ],
];
