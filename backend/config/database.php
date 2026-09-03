<?php

return [
    'default' => env('DB_CONNECTION', 'mysql'),

    'connections' => [
        // Conexão em memória usada pelos testes (phpunit.xml).
        'sqlite' => [
            'driver' => 'sqlite',
            'database' => env('DB_DATABASE', ':memory:'),
            'prefix' => '',
        ],

        'mysql' => [
            'driver' => 'mysql',
            'host' => env('DB_HOST', '127.0.0.1'),
            'port' => env('DB_PORT', '3306'),
            'database' => env('DB_DATABASE', 'lojacorr_seguros'),
            'username' => env('DB_USERNAME', 'lojacorr'),
            'password' => env('DB_PASSWORD', ''),
            'unix_socket' => env('DB_SOCKET', ''),
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
            'strict' => true,
            'engine' => null,
        ],

        'mongodb' => [
            'driver' => 'mongodb',
            'host' => env('MONGO_HOST', 'localhost'),
            'port' => env('MONGO_PORT', 27017),
            'database' => env('MONGO_DATABASE', 'lojacorr_logs'),
            'username' => env('MONGO_USERNAME', ''),
            'password' => env('MONGO_PASSWORD', ''),
            'options' => [
                'database' => env('MONGO_AUTH_DATABASE', 'admin'),
            ],
        ],
    ],

    'migrations' => 'migrations',

    'redis' => [
        'client' => 'predis',

        'default' => [
            'host' => env('REDIS_HOST', '127.0.0.1'),
            'password' => env('REDIS_PASSWORD', null),
            'port' => env('REDIS_PORT', 6379),
            'database' => 0,
        ],

        'cache' => [
            'host' => env('REDIS_HOST', '127.0.0.1'),
            'password' => env('REDIS_PASSWORD', null),
            'port' => env('REDIS_PORT', 6379),
            'database' => 1,
        ],
    ],
];
