<?php

namespace App\Repositories;

interface PersonalRecordRepositoryInterface
{
    public function getRankingByMovementId(int $movementId): array;
}