<?php

namespace App\Controllers;

use App\Services\RankingService;
use App\Utils\HttpStatus;
use Exception;

class RankingController extends Controller
{
    private RankingService $rankingService;

    public function __construct(RankingService $rankingService)
    {
        $this->rankingService = $rankingService;
    }

    /**
     * Get movement ranking
     * 
     * @param string $movementIdentifier Movement ID or name
     * @return void
     */
    public function getMovementRanking(string $movementIdentifier): void
    {
        try {
            // Set response headers
            $this->setJsonHeaders();

            $rankingService = $this->rankingService;

            // Validate input
            if (!$rankingService->isValidMovementIdentifier($movementIdentifier)) {
                $this->sendErrorResponse(
                    HttpStatus::BAD_REQUEST,
                    'Invalid movement identifier'
                );
                return;
            }

            // Get ranking data
            $rankingData = $rankingService->getMovementRanking($movementIdentifier);

            if ($rankingData === null) {
                $this->sendErrorResponse(
                    HttpStatus::NOT_FOUND,
                    'Movement not found'
                );
                return;
            }

            $this->sendSuccessResponse($rankingData);

        } catch (Exception $e) {
            error_log('Error in getMovementRanking: ' . $e->getMessage());
            $this->sendErrorResponse(
                HttpStatus::INTERNAL_SERVER_ERROR,
                'Internal server error'
            );
        }
    }
}

