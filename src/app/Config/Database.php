<?php

namespace App\Config;

use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Connection;
use PDO;

class Database
{
    private static ?Capsule $capsule = null;
    private static ?Connection $connection = null;

    public static function initialize(): void
    {
        if (self::$capsule !== null) {
            return;
        }

        self::$capsule = new Capsule;

        // Check if using SQLite for testing
        if (($_ENV['DB_CONNECTION'] ?? 'mysql') === 'sqlite') {
            self::$capsule->addConnection([
                'driver' => 'sqlite',
                'database' => $_ENV['DB_DATABASE'] ?? ':memory:',
                'prefix' => '',
            ]);
        } else {
            self::$capsule->addConnection([
                'driver' => 'mysql',
                'host' => $_ENV['DB_HOST'] ?? 'localhost',
                'database' => $_ENV['DB_NAME'] ?? 'movement_ranking',
                'username' => $_ENV['DB_USER'] ?? 'root',
                'password' => $_ENV['DB_PASSWORD'] ?? '',
                'port' => $_ENV['DB_PORT'] ?? 3306,
                'charset' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'prefix' => '',
                'options' => [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                    PDO::MYSQL_ATTR_INIT_COMMAND => "SET sql_mode='STRICT_TRANS_TABLES,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION'"
                ]
            ]);
        }

        self::$capsule->setAsGlobal();
        self::$capsule->bootEloquent();

        self::$connection = self::$capsule->getConnection();

        // Enable query logging for debugging (disable in production)
        if ($_ENV['APP_ENV'] === 'development') {
            self::$connection->enableQueryLog();
        }
    }

    public static function getConnection(): Connection
    {
        if (self::$connection === null) {
            self::initialize();
        }

        return self::$connection;
    }

    public static function getCapsule(): Capsule
    {
        if (self::$capsule === null) {
            self::initialize();
        }

        return self::$capsule;
    }

    public static function getQueryLog(): array
    {
        return self::getConnection()->getQueryLog();
    }
}

