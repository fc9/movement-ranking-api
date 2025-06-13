<?php

namespace Tests\Unit\Utils;

use App\Utils\Validator;
use PHPUnit\Framework\TestCase;

class ValidatorTest extends TestCase
{
    private string $testCacheDir;

    protected function setUp(): void
    {
        parent::setUp();
        $this->testCacheDir = __DIR__ . '/tmp_rate_limit';
        if (!is_dir($this->testCacheDir)) {
            mkdir($this->testCacheDir, 0777, true);
        }
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        array_map('unlink', glob($this->testCacheDir . '/rate_limit_*'));
        @rmdir($this->testCacheDir);
    }

    public function testSanitizeString(): void
    {
        $this->assertEquals('test', Validator::sanitizeString('  test  '));
        $this->assertEquals('test', Validator::sanitizeString("test\0"));
        $this->assertEquals('test', Validator::sanitizeString("test\x01"));
        $this->assertEquals("test\ttab", Validator::sanitizeString("test\ttab"));
        $this->assertEquals("test\nline", Validator::sanitizeString("test\nline"));
    }

    public function testIsValidMovementIdentifierValid(): void
    {
        $this->assertTrue(Validator::isValidMovementIdentifier('1'));
        $this->assertTrue(Validator::isValidMovementIdentifier('123'));
        $this->assertTrue(Validator::isValidMovementIdentifier('Deadlift'));
        $this->assertTrue(Validator::isValidMovementIdentifier('Back Squat'));
        $this->assertTrue(Validator::isValidMovementIdentifier('Bench-Press'));
        $this->assertTrue(Validator::isValidMovementIdentifier('Test_Movement'));
        $this->assertTrue(Validator::isValidMovementIdentifier('Movement123'));
    }

    public function testIsValidMovementIdentifierInvalid(): void
    {
        $this->assertFalse(Validator::isValidMovementIdentifier(''));
        $this->assertFalse(Validator::isValidMovementIdentifier('   '));
        $this->assertFalse(Validator::isValidMovementIdentifier('0'));
        $this->assertFalse(Validator::isValidMovementIdentifier('-1'));
        $this->assertFalse(Validator::isValidMovementIdentifier('test<script>'));
        $this->assertFalse(Validator::isValidMovementIdentifier('test;DROP'));
        $this->assertFalse(Validator::isValidMovementIdentifier('test@email'));
        $this->assertFalse(Validator::isValidMovementIdentifier(str_repeat('a', 256)));
    }

    public function testValidateAndSanitizeMovementIdentifier(): void
    {
        $this->assertEquals('test', Validator::validateAndSanitizeMovementIdentifier('  test  '));
        $this->assertEquals('123', Validator::validateAndSanitizeMovementIdentifier('123'));
        $this->assertNull(Validator::validateAndSanitizeMovementIdentifier(''));
        $this->assertNull(Validator::validateAndSanitizeMovementIdentifier('invalid<script>'));
    }

    public function testContainsSqlInjectionPatterns(): void
    {
        $this->assertTrue(Validator::containsSqlInjectionPatterns('SELECT * FROM users'));
        $this->assertTrue(Validator::containsSqlInjectionPatterns('DROP TABLE users'));
        $this->assertTrue(Validator::containsSqlInjectionPatterns('test; DELETE FROM'));
        $this->assertTrue(Validator::containsSqlInjectionPatterns("test' OR '1'='1"));
        $this->assertTrue(Validator::containsSqlInjectionPatterns('test--comment'));
        $this->assertTrue(Validator::containsSqlInjectionPatterns('test/*comment*/'));
        $this->assertTrue(Validator::containsSqlInjectionPatterns('test&param'));
        $this->assertTrue(Validator::containsSqlInjectionPatterns('test$var'));
        
        $this->assertFalse(Validator::containsSqlInjectionPatterns('Deadlift'));
        $this->assertFalse(Validator::containsSqlInjectionPatterns('Back Squat'));
        $this->assertFalse(Validator::containsSqlInjectionPatterns('123'));
    }

    public function testCheckRateLimit(): void
    {
        $ip          = '192.168.1.1';
        $otherIp     = '192.168.1.2';
        $maxRequests = 2;
        $timeWindow  = 3600; // 10 min
        
        // First request should be allowed
        $this->assertTrue(Validator::checkRateLimit($ip, $maxRequests, $timeWindow, $this->testCacheDir));
        
        // Second request should be allowed
        $this->assertTrue(Validator::checkRateLimit($ip, $maxRequests, $timeWindow, $this->testCacheDir));
        
        // Third request should be denied (limit is 2)
        $this->assertFalse(Validator::checkRateLimit($ip, $maxRequests, $timeWindow, $this->testCacheDir));
        
        // Different IP should be allowed
        $this->assertTrue(Validator::checkRateLimit($otherIp, $maxRequests, $timeWindow, $this->testCacheDir));
    }

    public function testCheckRateLimitTimeWindow(): void
    {
        $ip          = '192.168.1.100';
        $maxRequests = 1;
        $timeWindow  = 1;
        
        // Use very short time window for testing
        $this->assertTrue(Validator::checkRateLimit($ip, $maxRequests, $timeWindow));
        $this->assertFalse(Validator::checkRateLimit($ip, $maxRequests, $timeWindow));
        
        // Wait for time window to pass
        sleep(2);
        
        // Should be allowed again
        $this->assertTrue(Validator::checkRateLimit($ip, $maxRequests, $timeWindow));
    }
}

