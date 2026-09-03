<?php

namespace App\Repositories\Contracts;

interface SeguroRepositoryInterface
{
    public function all(array $filters = [], array $orderBy = [], int $perPage = 10);
    public function find(int $id, ?int $userId = null);
    public function create(array $data);
    public function update(int $id, int $userId, array $data);
    public function delete(int $id, int $userId);
    public function countByStatusVigencia(int $userId): array;
}
