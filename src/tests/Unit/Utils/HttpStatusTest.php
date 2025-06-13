<?php

namespace Tests\Unit\Utils;

use App\Utils\HttpStatus;
use PHPUnit\Framework\TestCase;

class HttpStatusTest extends TestCase
{
    public function testHttpStatusConstants()
    {
        // Test common HTTP status codes
        $this->assertEquals(200, HttpStatus::OK);
        $this->assertEquals(201, HttpStatus::CREATED);
        $this->assertEquals(400, HttpStatus::BAD_REQUEST);
        $this->assertEquals(401, HttpStatus::UNAUTHORIZED);
        $this->assertEquals(403, HttpStatus::FORBIDDEN);
        $this->assertEquals(404, HttpStatus::NOT_FOUND);
        $this->assertEquals(429, HttpStatus::TOO_MANY_REQUESTS);
        $this->assertEquals(500, HttpStatus::INTERNAL_SERVER_ERROR);
    }

    public function testGetReasonPhrase()
    {
        $this->assertEquals('OK', HttpStatus::getReasonPhrase(HttpStatus::OK));
        $this->assertEquals('Not Found', HttpStatus::getReasonPhrase(HttpStatus::NOT_FOUND));
        $this->assertEquals('Internal Server Error', HttpStatus::getReasonPhrase(HttpStatus::INTERNAL_SERVER_ERROR));
        $this->assertEquals('Too Many Requests', HttpStatus::getReasonPhrase(HttpStatus::TOO_MANY_REQUESTS));
        $this->assertEquals('Unknown Status Code', HttpStatus::getReasonPhrase(999));
    }

    public function testIsSuccessful()
    {
        $this->assertTrue(HttpStatus::isSuccessful(HttpStatus::OK));
        $this->assertTrue(HttpStatus::isSuccessful(HttpStatus::CREATED));
        $this->assertTrue(HttpStatus::isSuccessful(HttpStatus::ACCEPTED));
        $this->assertFalse(HttpStatus::isSuccessful(HttpStatus::BAD_REQUEST));
        $this->assertFalse(HttpStatus::isSuccessful(HttpStatus::INTERNAL_SERVER_ERROR));
    }

    public function testIsClientError()
    {
        $this->assertTrue(HttpStatus::isClientError(HttpStatus::BAD_REQUEST));
        $this->assertTrue(HttpStatus::isClientError(HttpStatus::NOT_FOUND));
        $this->assertTrue(HttpStatus::isClientError(HttpStatus::TOO_MANY_REQUESTS));
        $this->assertFalse(HttpStatus::isClientError(HttpStatus::OK));
        $this->assertFalse(HttpStatus::isClientError(HttpStatus::INTERNAL_SERVER_ERROR));
    }

    public function testIsServerError()
    {
        $this->assertTrue(HttpStatus::isServerError(HttpStatus::INTERNAL_SERVER_ERROR));
        $this->assertTrue(HttpStatus::isServerError(HttpStatus::BAD_GATEWAY));
        $this->assertTrue(HttpStatus::isServerError(HttpStatus::SERVICE_UNAVAILABLE));
        $this->assertFalse(HttpStatus::isServerError(HttpStatus::OK));
        $this->assertFalse(HttpStatus::isServerError(HttpStatus::BAD_REQUEST));
    }

    public function testIsError()
    {
        $this->assertTrue(HttpStatus::isError(HttpStatus::BAD_REQUEST));
        $this->assertTrue(HttpStatus::isError(HttpStatus::NOT_FOUND));
        $this->assertTrue(HttpStatus::isError(HttpStatus::INTERNAL_SERVER_ERROR));
        $this->assertFalse(HttpStatus::isError(HttpStatus::OK));
        $this->assertFalse(HttpStatus::isError(HttpStatus::CREATED));
    }

    public function testIsInformational()
    {
        $this->assertTrue(HttpStatus::isInformational(HttpStatus::CONTINUE));
        $this->assertTrue(HttpStatus::isInformational(HttpStatus::SWITCHING_PROTOCOLS));
        $this->assertFalse(HttpStatus::isInformational(HttpStatus::OK));
        $this->assertFalse(HttpStatus::isInformational(HttpStatus::BAD_REQUEST));
    }

    public function testIsRedirection()
    {
        $this->assertTrue(HttpStatus::isRedirection(HttpStatus::MOVED_PERMANENTLY));
        $this->assertTrue(HttpStatus::isRedirection(HttpStatus::FOUND));
        $this->assertTrue(HttpStatus::isRedirection(HttpStatus::TEMPORARY_REDIRECT));
        $this->assertFalse(HttpStatus::isRedirection(HttpStatus::OK));
        $this->assertFalse(HttpStatus::isRedirection(HttpStatus::BAD_REQUEST));
    }

    public function testGetAllStatusCodes()
    {
        $statusCodes = HttpStatus::getAllStatusCodes();

        $this->assertIsArray($statusCodes);
        $this->assertArrayHasKey('OK', $statusCodes);
        $this->assertArrayHasKey('NOT_FOUND', $statusCodes);
        $this->assertArrayHasKey('INTERNAL_SERVER_ERROR', $statusCodes);
        $this->assertEquals(200, $statusCodes['OK']);
        $this->assertEquals(404, $statusCodes['NOT_FOUND']);
        $this->assertEquals(500, $statusCodes['INTERNAL_SERVER_ERROR']);
    }
}

