<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\ViaCepService;

class CepController extends Controller
{
    protected $viaCepService;

    public function __construct(ViaCepService $viaCepService)
    {
        $this->viaCepService = $viaCepService;
    }

    public function buscar(string $cep)
    {
        $result = $this->viaCepService->buscarEndereco($cep);

        if (!$result['success']) {
            return $this->errorResponse(
                $result['message'],
                isset($result['timeout']) ? 504 : 404
            );
        }

        return $this->successResponse($result['data'], $result['message']);
    }
}
