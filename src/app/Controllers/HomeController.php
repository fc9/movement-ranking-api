<?php

namespace App\Controllers;

class HomeController extends Controller
{
    public function index(): void {
        $data = [
            'name' => 'Movement Ranking API',
            'version' => '1.0.0',
            'description' => 'RESTful API for movement ranking system',
            'endpoints' => [
                'GET /movements/{id}/ranking' => 'Get movement ranking by ID',
                'GET /movements/{name}/ranking' => 'Get movement ranking by name',
                'GET /health' => 'Health check endpoint'
            ],
            'timestamp' => date('c')
        ];

        $this->setJsonHeaders();
        $this->sendSuccessResponse($data);
    }
}