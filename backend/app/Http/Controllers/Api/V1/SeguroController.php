<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreSeguroRequest;
use App\Http\Requests\ListSeguroRequest;
use App\Http\Resources\SeguroResource;
use App\Services\SeguroService;

class SeguroController extends Controller
{
    protected $seguroService;

    public function __construct(SeguroService $seguroService)
    {
        $this->seguroService = $seguroService;
    }

    public function index(ListSeguroRequest $request)
    {
        $filters = $request->only([
            'documento',
            'status',
            'inicio_vigencia_de',
            'inicio_vigencia_ate',
            'fim_vigencia_de',
            'fim_vigencia_ate',
            'seguradora_id',
            'ramo_id',
        ]);

        $orderBy = [
            'sort_by' => $request->get('sort_by', 'created_at'),
            'sort_order' => $request->get('sort_order', 'desc'),
        ];

        $perPage = $request->get('per_page', 10);

        $seguros = $this->seguroService->listar($filters, $orderBy, $perPage);

        return $this->paginatedResponse($seguros, SeguroResource::class);
    }

    /** Contagens agregadas por status de vigência para o usuário atual. */
    public function summary()
    {
        $data = $this->seguroService->resumo(auth()->id());
        return $this->successResponse($data);
    }

    public function store(StoreSeguroRequest $request)
    {
        $seguro = $this->seguroService->criar($request->validated());

        return $this->successResponse(
            new SeguroResource($seguro),
            'Seguro contratado com sucesso',
            201
        );
    }

    public function show(int $id)
    {
        $seguro = $this->seguroService->buscar($id, auth()->id());
        return $this->successResponse(new SeguroResource($seguro));
    }

    public function update(StoreSeguroRequest $request, int $id)
    {
        $seguro = $this->seguroService->atualizar($id, $request->validated(), auth()->id());

        return $this->successResponse(
            new SeguroResource($seguro),
            'Seguro atualizado com sucesso'
        );
    }

    public function destroy(int $id)
    {
        $this->seguroService->deletar($id, auth()->id());
        return $this->successResponse(null, 'Seguro removido com sucesso');
    }
}
