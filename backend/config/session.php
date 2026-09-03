<?php

return [

    // Drivers suportados: file, cookie, database, apc, memcached, redis, dynamodb, array
    'driver' => env('SESSION_DRIVER', 'file'),

    // Minutos ociosos antes da sessão expirar
    'lifetime' => env('SESSION_LIFETIME', 120),

    'expire_on_close' => false,

    'encrypt' => false,

    // Apenas para o driver "file"
    'files' => storage_path('framework/sessions'),

    // Conexão usada pelos drivers "database" e "redis"
    'connection' => env('SESSION_CONNECTION', null),

    'table' => 'sessions',

    'store' => env('SESSION_STORE', null),

    // Chance de remoção de sessões expiradas: 2 em 100
    'lottery' => [2, 100],

    'cookie' => env(
        'SESSION_COOKIE',
        str_slug(env('APP_NAME', 'laravel'), '_').'_session'
    ),

    'path' => '/',

    'domain' => env('SESSION_DOMAIN', null),

    'secure' => env('SESSION_SECURE', false),

    'http_only' => true,

    // Mitiga CSRF em requisições cross-site: lax | strict | none | null
    'same_site' => null,

];
