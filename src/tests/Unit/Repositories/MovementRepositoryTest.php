<?php

namespace Tests\Unit\Repositories;

use App\Models\Movement;
use App\Repositories\MovementRepository;
use Tests\BaseTestCase;

class MovementRepositoryTest extends BaseTestCase
{
    private MovementRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new MovementRepository();
    }

    protected function tearDown(): void
    {
        $this->cleanTestData();
        parent::tearDown();
    }

    public function testFindById(): void
    {
        $movement = $this->repository->findById(1);
        
        $this->assertInstanceOf(Movement::class, $movement);
        $this->assertEquals(1, $movement->id);
        $this->assertEquals('Deadlift', $movement->name);
    }

    public function testFindByIdNotFound(): void
    {
        $movement = $this->repository->findById(999);
        
        $this->assertNull($movement);
    }

    public function testFindByName(): void
    {
        $movement = $this->repository->findByName('Deadlift');
        
        $this->assertInstanceOf(Movement::class, $movement);
        $this->assertEquals(1, $movement->id);
        $this->assertEquals('Deadlift', $movement->name);
    }

    public function testFindByNameNotFound(): void
    {
        $movement = $this->repository->findByName('Non Existent Movement');
        
        $this->assertNull($movement);
    }

    public function testFindByIdOrNameWithId(): void
    {
        $movement = $this->repository->findByIdOrName('1');
        
        $this->assertInstanceOf(Movement::class, $movement);
        $this->assertEquals(1, $movement->id);
        $this->assertEquals('Deadlift', $movement->name);
    }

    public function testFindByIdOrNameWithName(): void
    {
        $movement = $this->repository->findByIdOrName('Back Squat');
        
        $this->assertInstanceOf(Movement::class, $movement);
        $this->assertEquals(2, $movement->id);
        $this->assertEquals('Back Squat', $movement->name);
    }

    public function testFindByIdOrNameNotFound(): void
    {
        $movement = $this->repository->findByIdOrName('999');
        
        $this->assertNull($movement);
    }

    public function testFindByIdOrNameWithInvalidId(): void
    {
        $movement = $this->repository->findByIdOrName('999');
        
        $this->assertNull($movement);
    }
}

