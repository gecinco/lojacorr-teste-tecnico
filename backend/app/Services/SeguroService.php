<?php

namespace App\Services;

use App\Repositories\Contracts\SeguroRepositoryInterface;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class SeguroService
{
    /** TTL do cache do resumo por usuário, em segundos. */
    const SUMMARY_CACHE_TTL = 60;

    protected $repository;
    protected $auditService;

    public function __construct(
        SeguroRepositoryInterface $repository,
        AuditService $auditService
    ) {
        $this->repository = $repository;
        $this->auditService = $auditService;
    }

    public function listar(array $filters = [], array $orderBy = [], int $perPage = 10)
    {
        $filters['user_id'] = Auth::id();
        return $this->repository->all($filters, $orderBy, $perPage);
    }

    private function esquecerResumo(int $userId): void
    {
        Cache::forget("seguros:summary:{$userId}");
    }

    public function resumo(int $userId): array
    {
        return Cache::remember(
            "seguros:summary:{$userId}",
            self::SUMMARY_CACHE_TTL,
            function () use ($userId) {
                return $this->repository->countByStatusVigencia($userId);
            }
        );
    }

    public function buscar(int $id, int $userId)
    {
        return $this->repository->find($id, $userId);
    }

    public function criar(array $data)
    {
        $data['user_id'] = Auth::id();

        $seguro = DB::transaction(function () use ($data) {
            return $this->repository->create($data);
        });

        // Audit e invalidação de cache só se o INSERT foi confirmado.
        $this->auditService->log(
            'seguro_criado',
            'Seguro',
            $seguro->id,
            null,
            $seguro->toArray()
        );
        $this->esquecerResumo($seguro->user_id);

        return $seguro->load(['seguradora:id,nome,codigo', 'ramo:id,nome,codigo']);
    }

    public function atualizar(int $id, array $data, int $userId)
    {
        // Estado anterior capturado fora da transação para reduzir o lock.
        $seguroAntigo = $this->repository->find($id, $userId);
        $dadosAntigos = $seguroAntigo->toArray();

        $seguro = DB::transaction(function () use ($id, $data, $userId) {
            return $this->repository->update($id, $userId, $data);
        });

        $this->auditService->log(
            'seguro_atualizado',
            'Seguro',
            $seguro->id,
            $dadosAntigos,
            $seguro->toArray()
        );
        $this->esquecerResumo($userId);

        return $seguro;
    }

    public function deletar(int $id, int $userId)
    {
        $seguro = $this->repository->find($id, $userId);
        $dadosAntigos = $seguro->toArray();

        DB::transaction(function () use ($id, $userId) {
            $this->repository->delete($id, $userId);
        });

        $this->auditService->log(
            'seguro_deletado',
            'Seguro',
            $id,
            $dadosAntigos,
            null
        );
        $this->esquecerResumo($userId);

        return true;
    }
}
