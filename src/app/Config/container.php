<?php

use App\Repositories\MovementRepository;
use App\Repositories\PersonalRecordRepository;
use App\Repositories\MovementRepositoryInterface;
use App\Repositories\PersonalRecordRepositoryInterface;
use App\Services\RankingService;
use App\Utils\HttpStatus;
use DI\ContainerBuilder;

$builder = new ContainerBuilder();

// Configuração básica com autowiring
$builder->useAutowiring(true);

$builder->addDefinitions([
    RankingService::class => DI\create()
        ->constructor(
            DI\get(MovementRepositoryInterface::class),
            DI\get(PersonalRecordRepositoryInterface::class),
            DI\value((int)$_ENV['CACHE_TTL'] ?? 300)
        ),

    // Bind de interfaces
    MovementRepositoryInterface::class       => DI\get( MovementRepository::class),
    PersonalRecordRepositoryInterface::class => DI\get(PersonalRecordRepository::class)
]);

if ($_ENV['APP_ENV'] === 'production') {
    $builder->enableCompilation(__DIR__ . '/../var/cache');
}

try {
    return $builder->build();
} catch (Exception $e) {
    error_log('Fatal error: ' . $e->getMessage());

    http_response_code(HttpStatus::INTERNAL_SERVER_ERROR);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'success' => false,
        'error' => [
            'code' => HttpStatus::INTERNAL_SERVER_ERROR,
            'message' => 'Internal server error'
        ],
        'timestamp' => date('c')
    ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
}