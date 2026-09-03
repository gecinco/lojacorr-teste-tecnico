<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return response()->json([
        'name' => 'Lojacorr Seguros API',
        'version' => '1.0.0',
        'documentation' => '/api/v1',
    ]);
});
