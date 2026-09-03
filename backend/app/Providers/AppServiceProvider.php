<?php

namespace App\Providers;

use App\Http\Controllers\Api\V1\RamoController;
use App\Http\Controllers\Api\V1\SeguradoraController;
use App\Models\Ramo;
use App\Models\Seguradora;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function boot()
    {
        Schema::defaultStringLength(191);

        // Invalida cache de dados de referência ao criar/atualizar/remover.
        Seguradora::saved(function () {
            Cache::forget(SeguradoraController::CACHE_KEY);
        });
        Seguradora::deleted(function () {
            Cache::forget(SeguradoraController::CACHE_KEY);
        });

        Ramo::saved(function () {
            Cache::forget(RamoController::CACHE_KEY);
        });
        Ramo::deleted(function () {
            Cache::forget(RamoController::CACHE_KEY);
        });
    }

    public function register()
    {
        $this->app->singleton(\App\Services\ViaCepService::class);
    }
}
