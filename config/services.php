<?php

return [
    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key'    => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'keycloak' => [
        'client_id'      => env('KEYCLOAK_CLIENT_ID'),
        'client_secret'  => env('KEYCLOAK_CLIENT_SECRET'),
        'redirect'       => env('KEYCLOAK_REDIRECT_URI'),
        'base_url'       => env('KEYCLOAK_BASE_URL'),
        'realms'         => env('KEYCLOAK_REALM'),
        'internal_url'   => env('KEYCLOAK_INTERNAL_URL', env('KEYCLOAK_BASE_URL')),
        'admin_user'     => env('KEYCLOAK_ADMIN_USER', env('KC_ADMIN_USER', 'admin')),
        'admin_password' => env('KEYCLOAK_ADMIN_PASSWORD', env('KC_ADMIN_PASS', '')),
    ],

    'authorization_center' => [
        'url' => rtrim(env('AUTHORIZATION_CENTER_URL', env('AUTHORIZATION_SERVER_URL', 'http://localhost:8080')), '/'),
        'app_code' => env('AUTHORIZATION_CENTER_APP_CODE', env('AUTHORIZATION_APP_CODE', 'logstack-app')),
    ],

    'freeipa' => [
        'url'      => env('FREEIPA_URL', 'https://ipa.logstack.web.id'),
        'user'     => env('FREEIPA_ADMIN_USER', 'admin'),
        'password' => env('FREEIPA_ADMIN_PASSWORD'),
    ],

    'odoo' => [
        'url'      => env('ODOO_URL'),
        'db'       => env('ODOO_DB', 'odoo'),
        'username' => env('ODOO_SVC_USER'),
        'password' => env('ODOO_SVC_PASS'),
    ],

    'doveadm' => [
        'host'     => env('DOVEADM_HOST', '172.18.4.107'),
        'port'     => env('DOVEADM_PORT', 8888),
        'password' => env('DOVEADM_PASSWORD', ''),
        'domain'   => env('DOVEADM_MAIL_DOMAIN', 'logstack.web.id'),
    ],

    'nextcloud' => [
        'base_url'  => env('NEXTCLOUD_BASE_URL', 'http://172.18.4.105'),
        'api_user'  => env('NEXTCLOUD_API_USER', 'admin'),
        'api_token' => env('NEXTCLOUD_API_TOKEN', ''),
        'host'      => env('NEXTCLOUD_HOST', '172.18.4.105'),
        'ssh_key'   => env('NEXTCLOUD_SSH_KEY', '/var/www/.ssh/id_rsa'),
        'ssh_port'  => env('NEXTCLOUD_SSH_PORT', 2227),
        'ssh_user'  => env('NEXTCLOUD_SSH_USER', 'root'),
    ],

    'onlyoffice' => [
        'url'    => env('ONLYOFFICE_URL', 'http://172.18.4.112'),
        'secret' => env('ONLYOFFICE_SECRET', ''),
    ],

    'infrastructure' => [
        'mail_domain'     => env('SERVICE_MAIL_DOMAIN', 'logstack.web.id'),
        'prometheus_url'  => env('PROMETHEUS_URL', 'http://172.18.4.108:9090'),
        'protected_users' => env('PROTECTED_USERNAMES', 'admin,super-admin'),

        'url_nextcloud' => env('SERVICE_URL_NEXTCLOUD', 'https://drive.logstack.web.id'),
        'url_odoo'      => env('SERVICE_URL_ODOO', 'https://erp.logstack.web.id'),
        'url_sogo'      => env('SERVICE_URL_SOGO', 'https://mbox.logstack.web.id'),
        'url_freeipa'   => env('SERVICE_URL_FREEIPA', 'https://ipa.logstack.web.id'),
        'url_grafana'   => env('SERVICE_URL_GRAFANA', 'https://monit.logstack.web.id'),
        'url_keycloak'  => env('SERVICE_URL_KEYCLOAK', 'https://sso.logstack.web.id'),

        'ip_nextcloud'  => env('SERVICE_IP_NEXTCLOUD', '172.18.4.105'),
        'port_nextcloud'=> env('SERVICE_PORT_NEXTCLOUD', 80),
        'ip_odoo'       => env('SERVICE_IP_ODOO', '172.18.4.106'),
        'port_odoo'     => env('SERVICE_PORT_ODOO', 8069),
        'ip_sogo'       => env('SERVICE_IP_SOGO', '172.18.4.107'),
        'port_sogo'     => env('SERVICE_PORT_SOGO', 80),
        'ip_freeipa'    => env('SERVICE_IP_FREEIPA', '172.18.4.103'),
        'port_freeipa'  => env('SERVICE_PORT_FREEIPA', 443),
        'ip_grafana'    => env('SERVICE_IP_GRAFANA', '172.18.4.108'),
        'port_grafana'  => env('SERVICE_PORT_GRAFANA', 3000),
        'ip_nginx'      => env('SERVICE_IP_NGINX', '172.18.4.101'),
        'port_nginx'    => env('SERVICE_PORT_NGINX', 80),
    ],
];
