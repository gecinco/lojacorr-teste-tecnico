<?php

use Illuminate\Support\Facades\Route;

// Prefixo: /api/v1

// Rotas públicas — refresh fica fora de jwt.auth porque é usado justamente
// quando o token expirou (o Manager::refresh() aceita tokens expirados).
Route::middleware('throttle:10,1')->group(function () {
    Route::post('/auth/login', 'AuthController@login');
    Route::post('/auth/refresh', 'AuthController@refresh');
});

Route::middleware(['jwt.auth', 'throttle:120,1'])->group(function () {
    Route::prefix('auth')->group(function () {
        Route::post('/logout', 'AuthController@logout');
        Route::get('/me', 'AuthController@me');
    });

    // Antes do apiResource para não colidir com show(id)
    Route::get('/seguros/summary', 'SeguroController@summary');

    Route::apiResource('seguros', 'SeguroController');

    Route::get('/seguradoras', 'SeguradoraController@index');
    Route::get('/ramos', 'RamoController@index');
    Route::get('/cep/{cep}', 'CepController@buscar');
});
