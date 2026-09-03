<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\SeguradoraResource;
use App\Models\Seguradora;
use Illuminate\Support\Facades\Cache;

class SeguradoraController extends Controller
{
    /** Seguradoras ativas são dado de referência quase estático. */
    const CACHE_KEY = 'seguradoras:ativas';
    const CACHE_TTL = 3600;

    public function index()
    {
        $seguradoras = Cache::remember(self::CACHE_KEY, self::CACHE_TTL, function () {
            return Seguradora::ativas()
                ->orderBy('nome')
                ->get(['id', 'nome', 'codigo']);
        });

        return $this->successResponse(
            SeguradoraResource::collection($seguradoras),
            'Seguradoras listadas com sucesso'
        );
    }
}
