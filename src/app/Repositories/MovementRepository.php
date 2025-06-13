<?php

namespace App\Repositories;

use App\Models\Movement;

class MovementRepository implements MovementRepositoryInterface
{
    public function findById(int $id): ?Movement
    {
        return Movement::find($id);
    }

    public function findByName(string $name): ?Movement
    {
        return Movement::where('name', $name)->first();
    }

    public function findByIdOrName(string $identifier): ?Movement
    {
        // Try to find by ID first (if identifier is numeric)
        if (is_numeric($identifier)) {
            $movement = $this->findById((int) $identifier);
            if ($movement) {
                return $movement;
            }
        }

        // If not found by ID or identifier is not numeric, try by name
        return $this->findByName($identifier);
    }
}

