<?php

use App\Controllers\RankingController;
use App\Controllers\StatusController;
use App\Controllers\HomeController;
use App\Config\Router;

return function (Router $router, $container) {
    // Movement ranking endpoint
    $router->get('/movements/{identifier}/ranking', function($identifier) use ($container) {
        $controller = $container->get(RankingController::class);
        $controller->getMovementRanking(urldecode($identifier));
    });

    // Handle OPTIONS requests for CORS
    $router->options('/movements/{identifier}/ranking', function() use ($container) {
        $controller = $container->get(RankingController::class);
        $controller->handleOptions();
    });

    $router->get('/health', function() use ($container) {
        $controller = $container->get(StatusController::class);
        $controller->health();
    });

    $router->get('/', function () use ($container) {
        $controller = $container->get(HomeController::class);
        $controller->index();
    });
};