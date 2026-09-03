<?php

namespace App\Repositories\Contracts;

interface UserRepositoryInterface
{
    public function findByEmail(string $email);
    public function find(int $id);
}
