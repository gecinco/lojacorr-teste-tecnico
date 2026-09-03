<?php

return [
    'viacep' => [
        'base_url' => env('VIACEP_BASE_URL', 'https://viacep.com.br/ws'),
        'timeout' => env('VIACEP_TIMEOUT', 5000),
    ],
];
