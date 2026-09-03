<?php

namespace App\Repositories\Eloquent;

use App\Models\Seguro;
use App\Repositories\Contracts\SeguroRepositoryInterface;

class SeguroRepository implements SeguroRepositoryInterface
{
    /** Colunas usadas pelo SeguroResource - evita SELECT *. */
    private const LIST_COLUMNS = [
        'id', 'user_id', 'seguradora_id', 'ramo_id',
        'documento_segurado', 'tipo_documento', 'nome_segurado',
        'valor_total', 'quantidade_parcelas', 'valor_parcela',
        'inicio_vigencia', 'fim_vigencia',
        'cep', 'logradouro', 'numero', 'complemento',
        'bairro', 'cidade', 'uf',
        'created_at', 'updated_at',
    ];

    private const EAGER_LOAD = [
        'seguradora:id,nome,codigo',
        'ramo:id,nome,codigo',
    ];

    private const ALLOWED_SORT_FIELDS = [
        'id', 'documento_segurado', 'nome_segurado',
        'valor_total', 'quantidade_parcelas', 'valor_parcela',
        'inicio_vigencia', 'fim_vigencia', 'created_at',
    ];

    // Colunas exibidas que pertencem a tabelas relacionadas; ordenação via subquery escalar.
    private const RELATION_SORT_FIELDS = [
        'seguradora' => '(SELECT nome FROM seguradoras WHERE seguradoras.id = seguros.seguradora_id)',
        'ramo'       => '(SELECT nome FROM ramos WHERE ramos.id = seguros.ramo_id)',
    ];

    protected $model;

    public function __construct(Seguro $model)
    {
        $this->model = $model;
    }

    public function all(array $filters = [], array $orderBy = [], int $perPage = 10)
    {
        $query = $this->model
            ->newQuery()
            ->select(self::LIST_COLUMNS)
            ->with(self::EAGER_LOAD);

        $this->applyFilters($query, $filters);

        $sortBy = $orderBy['sort_by'] ?? 'created_at';
        $sortOrder = strtolower($orderBy['sort_order'] ?? 'desc') === 'asc' ? 'asc' : 'desc';

        if (isset(self::RELATION_SORT_FIELDS[$sortBy])) {
            $query->orderByRaw(self::RELATION_SORT_FIELDS[$sortBy] . ' ' . $sortOrder);
        } elseif ($sortBy === 'status') {
            $hoje = date('Y-m-d');
            $query->orderByRaw(
                'CASE WHEN fim_vigencia < ? THEN 0 WHEN inicio_vigencia > ? THEN 1 ELSE 2 END ' . $sortOrder,
                [$hoje, $hoje]
            );
        } elseif (in_array($sortBy, self::ALLOWED_SORT_FIELDS, true)) {
            $query->orderBy($sortBy, $sortOrder);
        }

        return $query->paginate($perPage);
    }

    public function find(int $id, ?int $userId = null)
    {
        $query = $this->model
            ->newQuery()
            ->select(self::LIST_COLUMNS)
            ->with(self::EAGER_LOAD);

        if ($userId !== null) {
            $query->where('user_id', $userId);
        }

        return $query->findOrFail($id);
    }

    public function create(array $data)
    {
        return $this->model->create($data);
    }

    // Escopado por user_id: nunca cruza registros entre usuários (anti-IDOR).
    public function update(int $id, int $userId, array $data)
    {
        $updated = $this->model
            ->newQuery()
            ->where('id', $id)
            ->where('user_id', $userId)
            ->update($data);

        if ($updated === 0) {
            // Distingue "não encontrado / de outro usuário" (404) de "nada mudou".
            $this->find($id, $userId);
        }

        return $this->find($id, $userId);
    }

    public function delete(int $id, int $userId)
    {
        $affected = $this->model
            ->newQuery()
            ->where('id', $id)
            ->where('user_id', $userId)
            ->delete();

        if ($affected === 0) {
            $this->find($id, $userId);
        }

        return true;
    }

    /** Contagens por status de vigência em uma única query. */
    public function countByStatusVigencia(int $userId): array
    {
        $today = date('Y-m-d');

        $row = $this->model
            ->newQuery()
            ->selectRaw(
                'COUNT(*) as total,
                 SUM(CASE WHEN inicio_vigencia > ? THEN 1 ELSE 0 END) as a_vencer,
                 SUM(CASE WHEN fim_vigencia < ? THEN 1 ELSE 0 END) as vencido,
                 SUM(CASE WHEN inicio_vigencia <= ? AND fim_vigencia >= ? THEN 1 ELSE 0 END) as vigente',
                [$today, $today, $today, $today]
            )
            ->where('user_id', $userId)
            ->first();

        return [
            'total'    => (int) ($row->total ?? 0),
            'vigente'  => (int) ($row->vigente ?? 0),
            'a_vencer' => (int) ($row->a_vencer ?? 0),
            'vencido'  => (int) ($row->vencido ?? 0),
        ];
    }

    private function applyFilters($query, array $filters): void
    {
        if (!empty($filters['user_id'])) {
            $query->where('user_id', $filters['user_id']);
        }

        if (!empty($filters['documento'])) {
            $query->byDocumento($filters['documento']);
        }

        // Mesma semântica de datas de Seguro::getStatusVigenciaAttribute.
        if (!empty($filters['status'])) {
            $hoje = date('Y-m-d');

            switch ($filters['status']) {
                case 'vigente':
                    $query->where('inicio_vigencia', '<=', $hoje)
                          ->where('fim_vigencia', '>=', $hoje);
                    break;
                case 'a_vencer':
                    $query->where('inicio_vigencia', '>', $hoje);
                    break;
                case 'vencido':
                    $query->where('fim_vigencia', '<', $hoje);
                    break;
            }
        }

        if (!empty($filters['inicio_vigencia_de'])) {
            $query->where('inicio_vigencia', '>=', $filters['inicio_vigencia_de']);
        }

        if (!empty($filters['inicio_vigencia_ate'])) {
            $query->where('inicio_vigencia', '<=', $filters['inicio_vigencia_ate']);
        }

        if (!empty($filters['fim_vigencia_de'])) {
            $query->where('fim_vigencia', '>=', $filters['fim_vigencia_de']);
        }

        if (!empty($filters['fim_vigencia_ate'])) {
            $query->where('fim_vigencia', '<=', $filters['fim_vigencia_ate']);
        }

        if (!empty($filters['seguradora_id'])) {
            $query->bySeguradora($filters['seguradora_id']);
        }

        if (!empty($filters['ramo_id'])) {
            $query->byRamo($filters['ramo_id']);
        }
    }
}
