<?php

namespace Tests;

use Exception;
use PHPUnit\Framework\TestCase;
use App\Config\Database;
use Illuminate\Database\Connection;

abstract class BaseTestCase extends TestCase
{
    protected static Connection $connection;

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        // Set test environment variables
        $_ENV["APP_ENV"]       = "testing";
        $_ENV['DB_CONNECTION'] = 'sqlite';
        $_ENV['DB_DATABASE']   = ':memory:';

        try {
            Database::initialize();
            static::$connection = Database::getConnection();
            static::createSqliteSchema();
        } catch (Exception $e) {
            self::markTestSkipped('Database connection failed: ' . $e->getMessage());
        }
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->cleanTestData();
        $this->createTestData();
    }

    protected function tearDown(): void
    {
        $this->cleanTestData();
        parent::tearDown();
    }

    /**
     * Create SQLite schema for testing.
     */
    protected static function createSqliteSchema(): void
    {
        $schema = [
            "CREATE TABLE user (id INTEGER PRIMARY KEY AUTOINCREMENT, name VARCHAR(255) NOT NULL)",
            "CREATE TABLE movement (id INTEGER PRIMARY KEY AUTOINCREMENT, name VARCHAR(255) NOT NULL)",
            "CREATE TABLE personal_record (id INTEGER PRIMARY KEY AUTOINCREMENT, user_id INTEGER NOT NULL, movement_id INTEGER NOT NULL, value REAL NOT NULL, date DATETIME NOT NULL)",
            "CREATE INDEX personal_record_user_id_idx ON personal_record (user_id)",
            "CREATE INDEX personal_record_movement_id_idx ON personal_record (movement_id)",
            "CREATE INDEX personal_record_value_idx ON personal_record (value)",
            "CREATE INDEX personal_record_date_idx ON personal_record (date)",
            "CREATE UNIQUE INDEX personal_record_user_movement_unique ON personal_record (user_id, movement_id, date)",
            "CREATE UNIQUE INDEX user_name_unique ON user (name)",
            "CREATE UNIQUE INDEX movement_name_unique ON movement (name)"
        ];

        foreach ($schema as $sql) {
            static::$connection->statement($sql);
        }
    }

    /**
     * Create test data in database
     */
    protected function createTestData(): void
    {
        // Insert test users
        static::$connection->table('user')->insert([
            ['id' => 1, 'name' => 'Joao'],
            ['id' => 2, 'name' => 'Jose'],
            ['id' => 3, 'name' => 'Paulo']
        ]);

        // Insert test movements
        static::$connection->table("movement")->insert([
            ["id" => 1, "name" => "Deadlift"],
            ["id" => 2, "name" => "Back Squat"],
            ["id" => 3, "name" => "Bench Press"]
        ]);

        // Insert test personal records
        static::$connection->table("personal_record")->insert([
            ["id" => 1, "user_id" => 1, "movement_id" => 1, "value" => 100.0, "date" => "2021-01-01 00:00:00"],
            ["id" => 2, "user_id" => 1, "movement_id" => 1, "value" => 180.0, "date" => "2021-01-02 00:00:00"],
            ["id" => 3, "user_id" => 1, "movement_id" => 1, "value" => 150.0, "date" => "2021-01-03 00:00:00"],
            ["id" => 4, "user_id" => 1, "movement_id" => 1, "value" => 110.0, "date" => "2021-01-04 00:00:00"],
            ["id" => 5, "user_id" => 2, "movement_id" => 1, "value" => 110.0, "date" => "2021-01-04 00:00:00"],
            ["id" => 6, "user_id" => 2, "movement_id" => 1, "value" => 140.0, "date" => "2021-01-05 00:00:00"],
            ["id" => 7, "user_id" => 2, "movement_id" => 1, "value" => 190.0, "date" => "2021-01-06 00:00:00"],
            ["id" => 8, "user_id" => 3, "movement_id" => 1, "value" => 170.0, "date" => "2021-01-01 00:00:00"],
            ["id" => 9, "user_id" => 3, "movement_id" => 1, "value" => 120.0, "date" => "2021-01-02 00:00:00"],
            ["id" => 10, "user_id" => 3, "movement_id" => 1, "value" => 130.0, "date" => "2021-01-03 00:00:00"],
            ["id" => 11, "user_id" => 1, "movement_id" => 2, "value" => 130.0, "date" => "2021-01-03 00:00:00"],
            ["id" => 12, "user_id" => 2, "movement_id" => 2, "value" => 130.0, "date" => "2021-01-03 00:00:00"],
            ["id" => 13, "user_id" => 3, "movement_id" => 2, "value" => 125.0, "date" => "2021-01-03 00:00:00"],
            ["id" => 14, "user_id" => 1, "movement_id" => 2, "value" => 110.0, "date" => "2021-01-05 00:00:00"],
            ["id" => 15, "user_id" => 1, "movement_id" => 2, "value" => 100.0, "date" => "2021-01-01 00:00:00"],
            ["id" => 16, "user_id" => 2, "movement_id" => 2, "value" => 120.0, "date" => "2021-01-01 00:00:00"],
            ["id" => 17, "user_id" => 3, "movement_id" => 2, "value" => 120.0, "date" => "2021-01-01 00:00:00"]
        ]);
    }

    /**
     * Clean test data from database
     */
    protected function cleanTestData(): void
    {
        static::$connection->table('personal_record')->truncate();
        static::$connection->table('movement')->truncate();
        static::$connection->table('user')->truncate();
    }
}

