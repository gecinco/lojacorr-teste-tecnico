<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\RamoResource;
use App\Models\Ramo;
use Illuminate\Support\Facades\Cache;

class RamoController extends Controller
{
    const CACHE_KEY = 'ramos:ativos';
    const CACHE_TTL = 3600;

    public function index()
    {
        $ramos = Cache::remember(self::CACHE_KEY, self::CACHE_TTL, function () {
            return Ramo::ativos()
                ->orderBy('nome')
                ->get(['id', 'nome', 'codigo']);
        });

        return $this->successResponse(
            RamoResource::collection($ramos),
            'Ramos listados com sucesso'
        );
    }
}
