<?php

namespace App\Controllers;

class StatusController extends Controller
{
    public function health(): void {
        $data = [
            'status' => 'healthy',
            'version' => '1.0.0'
        ];

        $this->setJsonHeaders();
        $this->sendSuccessResponse($data);
    }
}