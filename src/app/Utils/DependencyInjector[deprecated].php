<?php

namespace App\Utils;

use App\Repositories\MovementRepository;
use App\Repositories\PersonalRecordRepository;
use App\Services\RankingService;
use App\Controllers\RankingController;
use App\Controllers\StatusController;
use App\Controllers\HomeController;

class DependencyInjector
{
    private static array $instances = [];

    public static function resolve(string $className): object
    {
        if (isset(self::$instances[$className])) {
            return self::$instances[$className];
        }

        return self::createInstance($className);
    }

    private static function createInstance(string $className): object
    {
        switch ($className) {
            case RankingController::class:
                $rankingService = self::resolve(RankingService::class);
                return new RankingController($rankingService);

            case RankingService::class:
                $movementRepo = self::resolve(MovementRepository::class);
                $personalRecordRepo = self::resolve(PersonalRecordRepository::class);
                return new RankingService($movementRepo, $personalRecordRepo);

            case MovementRepository::class:
                return new MovementRepository();

            case PersonalRecordRepository::class:
                return new PersonalRecordRepository();

            case StatusController::class:
                return new StatusController();

            case HomeController::class:
                return new HomeController();

            default:
                throw new \RuntimeException("Class {$className} cannot be resolved by DependencyInjector");
        }
    }
}