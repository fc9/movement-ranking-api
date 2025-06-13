<?php

namespace App\Repositories;

use App\Models\Movement;

interface MovementRepositoryInterface
{
    public function findById(int $id): ?Movement;

    public function findByName(string $name): ?Movement;

    public function findByIdOrName(string $identifier): ?Movement;
}