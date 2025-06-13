<?php

namespace Tests\Unit\Repositories;

use App\Repositories\PersonalRecordRepository;
use Tests\BaseTestCase;

class PersonalRecordRepositoryTest extends BaseTestCase
{
    private PersonalRecordRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new PersonalRecordRepository();
        $this->createTestData();
    }

    protected function tearDown(): void
    {
        $this->cleanTestData();
        parent::tearDown();
    }

    public function testGetRankingByMovementId(): void
    {
        $ranking = $this->repository->getRankingByMovementId(1);
        
        $this->assertIsArray($ranking);
        $this->assertCount(3, $ranking);
        
        // Check first place (tied at 150.0)
        $firstPlace = $ranking[0];
        $this->assertEquals(1, $firstPlace['ranking_position']);
        $this->assertEquals(150.0, $firstPlace['personal_record']);
        $this->assertContains($firstPlace['user_name'], ['Test User 1', 'Test User 3']);
        
        // Check that ranking is ordered by value descending
        $this->assertGreaterThanOrEqual($ranking[1]['personal_record'], $firstPlace['personal_record']);
        $this->assertGreaterThanOrEqual($ranking[2]['personal_record'], $ranking[1]['personal_record']);
    }

    public function testGetRankingByMovementIdWithTies(): void
    {
        $ranking = $this->repository->getRankingByMovementId(1);
        
        // Find users with 150.0 value (should be tied for first place)
        $firstPlaceUsers = array_filter($ranking, function($user) {
            return $user['personal_record'] == 150.0;
        });
        
        $this->assertCount(2, $firstPlaceUsers);
        
        // Both should have ranking position 1
        foreach ($firstPlaceUsers as $user) {
            $this->assertEquals(1, $user['ranking_position']);
        }
    }

    public function testGetRankingByMovementIdEmpty(): void
    {
        $ranking = $this->repository->getRankingByMovementId(999);
        
        $this->assertIsArray($ranking);
        $this->assertEmpty($ranking);
    }

    public function testRankingStructure(): void
    {
        $ranking = $this->repository->getRankingByMovementId(1);
        
        $this->assertNotEmpty($ranking);
        
        $firstUser = $ranking[0];
        $this->assertArrayHasKey('user_id', $firstUser);
        $this->assertArrayHasKey('user_name', $firstUser);
        $this->assertArrayHasKey('personal_record', $firstUser);
        $this->assertArrayHasKey('personal_record_date', $firstUser);
        $this->assertArrayHasKey('ranking_position', $firstUser);
        
        $this->assertIsInt($firstUser['user_id']);
        $this->assertIsString($firstUser['user_name']);
        $this->assertIsFloat($firstUser['personal_record']);
        $this->assertIsString($firstUser['personal_record_date']);
        $this->assertIsInt($firstUser['ranking_position']);
    }
}

