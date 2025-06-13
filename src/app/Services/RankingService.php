<?php

namespace App\Services;

use App\Repositories\MovementRepositoryInterface;
use App\Repositories\PersonalRecordRepositoryInterface;
use App\Utils\Cache;
use App\Utils\Validator;

class RankingService
{
    private MovementRepositoryInterface $movementRepository;
    private PersonalRecordRepositoryInterface $personalRecordRepository;
    private int $cacheTtl;

    public function __construct(
        MovementRepositoryInterface $movementRepository,
        PersonalRecordRepositoryInterface $personalRecordRepository,
        int $cacheTtl = 300
    ) {
        $this->movementRepository = $movementRepository;
        $this->personalRecordRepository = $personalRecordRepository;
        $this->cacheTtl = $cacheTtl;
    }

    /**
     * Get movement ranking by movement identifier (ID or name)
     * 
     * @param string $movementIdentifier Movement ID or name
     * @return array|null Returns ranking data or null if movement not found
     */
    public function getMovementRanking(string $movementIdentifier): ?array
    {
        // Validate and sanitize input
        $sanitizedIdentifier = Validator::validateAndSanitizeMovementIdentifier($movementIdentifier);
        
        if ($sanitizedIdentifier === null) {
            return null;
        }

        // Check for SQL injection patterns
        if (Validator::containsSqlInjectionPatterns($sanitizedIdentifier)) {
            return null;
        }

        // Try to get from cache first
        $cacheKey = 'movement_ranking_' . md5($sanitizedIdentifier);
        
        return Cache::remember($cacheKey, function() use ($sanitizedIdentifier) {
            // Find movement by ID or name
            $movement = $this->movementRepository->findByIdOrName($sanitizedIdentifier);
            
            if (!$movement) {
                return null;
            }

            // Get ranking data
            $rankingData = $this->personalRecordRepository->getRankingByMovementId($movement->id);

            return [
                'movement' => [
                    'id' => $movement->id,
                    'name' => $movement->name
                ],
                'ranking' => $rankingData
            ];
        }, $this->cacheTtl);
    }

    /**
     * Validate movement identifier
     * 
     * @param string $identifier
     * @return bool
     */
    public function isValidMovementIdentifier(string $identifier): bool
    {
        return Validator::isValidMovementIdentifier($identifier);
    }

    /**
     * Clear cache for a specific movement
     * 
     * @param string $movementIdentifier
     * @return bool
     */
    public function clearMovementCache(string $movementIdentifier): bool
    {
        $cacheKey = 'movement_ranking_' . md5($movementIdentifier);
        return Cache::delete($cacheKey);
    }

    /**
     * Clear all ranking cache
     * 
     * @return bool
     */
    public function clearAllCache(): bool
    {
        return Cache::clear();
    }
}

