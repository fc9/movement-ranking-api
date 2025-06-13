<?php

namespace Tests\Integration;

use Tests\BaseTestCase;
use App\Controllers\RankingController;
use App\Services\RankingService;
use App\Repositories\MovementRepository;
use App\Repositories\PersonalRecordRepository;

class RankingControllerTest extends BaseTestCase
{
    private RankingController $controller;

    protected function setUp(): void
    {
        parent::setUp();

        try {
            $movementRepository       = new MovementRepository();
            $personalRecordRepository = new PersonalRecordRepository();
            $cacheTTL                 = 0;
            $rankingService           = new RankingService($movementRepository, $personalRecordRepository, $cacheTTL);
            $this->controller         = new RankingController($rankingService);

        } catch (\Exception $e) {
            parent::markTestSkipped('Database connection failed: ' . $e->getMessage());
        }
    }

    protected function tearDown(): void
    {
        $this->cleanTestData();
        parent::tearDown();
    }

    public function testGetMovementRankingSuccess(): void
    {
        ob_start();
        $this->controller->getMovementRanking('1');
        $output = ob_get_clean();

        $response = json_decode($output, true);

        $this->assertIsArray($response);
        $this->assertTrue($response['success']);
        $this->assertArrayHasKey('data', $response);
        $this->assertArrayHasKey('timestamp', $response);

        $data = $response['data'];
        $this->assertArrayHasKey('movement', $data);
        $this->assertArrayHasKey('ranking', $data);

        $this->assertEquals(1, $data['movement']['id']);
        $this->assertEquals('Deadlift', $data['movement']['name']);
        $this->assertIsArray($data['ranking']);
    }

    public function testGetMovementRankingByName(): void
    {
        ob_start();
        $this->controller->getMovementRanking('Deadlift');
        $output = ob_get_clean();

        $response = json_decode($output, true);

        $this->assertIsArray($response);
        $this->assertTrue($response['success']);
        $this->assertEquals('Deadlift', $response['data']['movement']['name']);
    }

    public function testGetMovementRankingNotFound(): void
    {
        ob_start();
        $this->controller->getMovementRanking('999');
        $output = ob_get_clean();

        $response = json_decode($output, true);

        $this->assertIsArray($response);
        $this->assertFalse($response['success']);
        $this->assertEquals(404, $response['error']['code']);
        $this->assertEquals('Movement not found', $response['error']['message']);
    }

    public function testGetMovementRankingInvalidIdentifier(): void
    {
        ob_start();
        $this->controller->getMovementRanking('');
        $output = ob_get_clean();

        $response = json_decode($output, true);

        $this->assertIsArray($response);
        $this->assertFalse($response['success']);
        $this->assertEquals(400, $response['error']['code']);
        $this->assertEquals('Invalid movement identifier', $response['error']['message']);
    }

    public function testGetMovementRankingSqlInjection(): void
    {
        ob_start();
        $this->controller->getMovementRanking("1'; DROP TABLE users; --");
        $output = ob_get_clean();

        $response = json_decode($output, true);

        $this->assertIsArray($response);
        $this->assertFalse($response['success']);
        $this->assertEquals(400, $response['error']['code']);
    }

    public function testHandleOptions(): void
    {
        ob_start();
        $this->controller->handleOptions();
        $output = ob_get_clean();

        $this->assertEmpty($output);
        
        // Check that CORS headers would be set (we can't test headers directly in unit tests)
        $this->assertTrue(true); // Placeholder assertion
    }

    public function testRankingDataStructure(): void
    {
        ob_start();
        $this->controller->getMovementRanking('1');
        $output = ob_get_clean();

        $response = json_decode($output, true);
        $ranking = $response['data']['ranking'];

        $this->assertNotEmpty($ranking);

        foreach ($ranking as $user) {
            $this->assertArrayHasKey('user_id', $user);
            $this->assertArrayHasKey('user_name', $user);
            $this->assertArrayHasKey('personal_record', $user);
            $this->assertArrayHasKey('personal_record_date', $user);
            $this->assertArrayHasKey('ranking_position', $user);

            $this->assertIsInt($user['user_id']);
            $this->assertIsString($user['user_name']);
            $this->assertIsNumeric($user['personal_record']);
            $this->assertIsString($user['personal_record_date']);
            $this->assertIsInt($user['ranking_position']);
        }
    }

    public function testRankingOrder(): void
    {
        ob_start();
        $this->controller->getMovementRanking('1');
        $output = ob_get_clean();

        $response = json_decode($output, true);
        $ranking = $response['data']['ranking'];

        // Check that ranking is ordered by personal record descending
        for ($i = 1; $i < count($ranking); $i++) {
            $this->assertGreaterThanOrEqual(
                $ranking[$i]['personal_record'],
                $ranking[$i-1]['personal_record']
            );
        }
    }
}

