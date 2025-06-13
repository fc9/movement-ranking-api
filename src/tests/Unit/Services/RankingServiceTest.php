<?php

namespace  Tests\Unit\Services;

use App\Models\Movement;
use App\Repositories\MovementRepositoryInterface;
use App\Repositories\PersonalRecordRepositoryInterface;
use App\Services\RankingService;
use Mockery;
use PHPUnit\Framework\TestCase;

class RankingServiceTest extends TestCase
{
    private RankingService $service;
    private MovementRepositoryInterface $movementRepository;
    private PersonalRecordRepositoryInterface $personalRecordRepository;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->movementRepository = Mockery::mock(MovementRepositoryInterface::class);
        $this->personalRecordRepository = Mockery::mock(PersonalRecordRepositoryInterface::class);
        
        $this->service = new RankingService(
            $this->movementRepository,
            $this->personalRecordRepository,
            0 // Disable cache for testing
        );
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function testGetMovementRankingSuccess(): void
    {
        $movement = new Movement();
        $movement->id = 1;
        $movement->name = 'Deadlift';

        $rankingData = [
            [
                'user_id' => 1,
                'user_name' => 'Joao',
                'personal_record' => 150.0,
                'personal_record_date' => '2021-01-01 00:00:00',
                'ranking_position' => 1
            ]
        ];

        $this->movementRepository
            ->shouldReceive('findByIdOrName')
            ->with('1')
            ->once()
            ->andReturn($movement);

        $this->personalRecordRepository
            ->shouldReceive('getRankingByMovementId')
            ->with(1)
            ->once()
            ->andReturn($rankingData);

        $result = $this->service->getMovementRanking('1');

        $this->assertIsArray($result);
        $this->assertArrayHasKey('movement', $result);
        $this->assertArrayHasKey('ranking', $result);
        
        $this->assertEquals(1, $result['movement']['id']);
        $this->assertEquals('Deadlift', $result['movement']['name']);
        $this->assertEquals($rankingData, $result['ranking']);
    }

    public function testGetMovementRankingMovementNotFound(): void
    {
        $this->movementRepository
            ->shouldReceive('findByIdOrName')
            ->with('999')
            ->once()
            ->andReturn(null);

        $result = $this->service->getMovementRanking('999');

        $this->assertNull($result);
    }

    public function testGetMovementRankingInvalidIdentifier(): void
    {
        $result = $this->service->getMovementRanking('');
        $this->assertNull($result);

        $result = $this->service->getMovementRanking('invalid<script>');
        $this->assertNull($result);
    }

    public function testIsValidMovementIdentifierValid(): void
    {
        $this->assertTrue($this->service->isValidMovementIdentifier('1'));
        $this->assertTrue($this->service->isValidMovementIdentifier('123'));
        $this->assertTrue($this->service->isValidMovementIdentifier('Deadlift'));
        $this->assertTrue($this->service->isValidMovementIdentifier('Back Squat'));
        $this->assertTrue($this->service->isValidMovementIdentifier('Bench-Press'));
        $this->assertTrue($this->service->isValidMovementIdentifier('Test_Movement'));
    }

    public function testIsValidMovementIdentifierInvalid(): void
    {
        $this->assertFalse($this->service->isValidMovementIdentifier(''));
        $this->assertFalse($this->service->isValidMovementIdentifier('   '));
        $this->assertFalse($this->service->isValidMovementIdentifier('0'));
        $this->assertFalse($this->service->isValidMovementIdentifier('-1'));
        $this->assertFalse($this->service->isValidMovementIdentifier('test<script>'));
        $this->assertFalse($this->service->isValidMovementIdentifier('test;DROP TABLE'));
        $this->assertFalse($this->service->isValidMovementIdentifier(str_repeat('a', 256))); // Too long
    }

    public function testGetMovementRankingByName(): void
    {
        $movement = new Movement();
        $movement->id = 1;
        $movement->name = 'Deadlift';

        $rankingData = [];

        $this->movementRepository
            ->shouldReceive('findByIdOrName')
            ->with('Deadlift')
            ->once()
            ->andReturn($movement);

        $this->personalRecordRepository
            ->shouldReceive('getRankingByMovementId')
            ->with(1)
            ->once()
            ->andReturn($rankingData);

        $result = $this->service->getMovementRanking('Deadlift');

        $this->assertIsArray($result);
        $this->assertEquals('Deadlift', $result['movement']['name']);
    }
}

