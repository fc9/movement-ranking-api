<?php

declare(strict_types=1);

namespace Tests\Unit\Controllers;

use App\Controllers\Controller;
use App\Utils\HttpStatus;
use PHPUnit\Framework\TestCase;

/**
 * Unit tests for BaseController
 */
class BaseControllerTest extends TestCase
{
    private static array $headers = [];
    private static ?int $responseCode = null;

    public static function setHeader(string $header): void
    {
        self::$headers[] = $header;
    }

    public static function setResponseCode(?int $code): ?int
    {
        self::$responseCode = $code;
        return $code;
    }

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        // Override header() and http_response_code() in App\Controllers namespace
        eval('namespace App\\Controllers; function header($header) { \\Tests\\Unit\\Controllers\\BaseControllerTest::setHeader($header); }');
        eval('namespace App\\Controllers; function http_response_code($code = null) { return \\Tests\\Unit\\Controllers\\BaseControllerTest::setResponseCode($code); }');
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        self::$headers = [];
        self::$responseCode = null;
        $_GET = [];
        $_POST = [];
        unset($_SERVER['REQUEST_METHOD'], $_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD'], $_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']);
    }

    private Controller $controller;

    protected function setUp(): void
    {
        parent::setUp();

        $this->controller = new class extends Controller {
            public function exposeSetJsonHeaders(int $cacheTime = 300): void
            {
                $this->setJsonHeaders($cacheTime);
            }

            public function exposeSendSuccessResponse(mixed $data = null, int $statusCode = HttpStatus::OK, array $meta = []): void
            {
                $this->sendSuccessResponse($data, $statusCode, $meta);
            }

            public function exposeSendErrorResponse(int $statusCode = HttpStatus::BAD_REQUEST, string $message = '', array $details = []): void
            {
                $this->sendErrorResponse($statusCode, $message, $details);
            }

            public function exposeValidateParams(array $params, array $rules): ?array
            {
                return $this->validateParams($params, $rules);
            }

            public function exposeGetParam(string $name, mixed $default = null, string $source = 'any'): mixed
            {
                return $this->getParam($name, $default, $source);
            }
        };
    }

    public function testSetJsonHeadersSetsContentTypeAndCacheControlDefault(): void
    {
        $this->controller->exposeSetJsonHeaders();

        $this->assertContains('Content-Type: application/json; charset=utf-8', self::$headers);
        $this->assertContains('Cache-Control: public, max-age=300', self::$headers);
    }

    public function testSetJsonHeadersWithCustomCacheTime(): void
    {
        $this->controller->exposeSetJsonHeaders(600);

        $this->assertContains('Cache-Control: public, max-age=600', self::$headers);
    }

    public function testSendSuccessResponseOutputsJsonAndSetsHttpCodeAndHeaders(): void
    {
        $data = ['foo' => 'bar'];

        ob_start();
        $this->controller->exposeSendSuccessResponse($data, HttpStatus::CREATED, []);
        $output = ob_get_clean();

        $decoded = json_decode($output, true);
        $this->assertIsArray($decoded);
        $this->assertTrue($decoded['success']);
        $this->assertSame($data, $decoded['data']);
        $this->assertArrayHasKey('timestamp', $decoded);

        $this->assertSame(HttpStatus::CREATED, self::$responseCode);
    }

    public function testSendErrorResponseOutputsJsonAndSetsHttpCode(): void
    {
        $message = 'Error occurred';

        ob_start();
        $this->controller->exposeSendErrorResponse(HttpStatus::BAD_REQUEST, $message, []);
        $output = ob_get_clean();

        $decoded = json_decode($output, true);
        $this->assertIsArray($decoded);
        $this->assertFalse($decoded['success']);
        $this->assertArrayHasKey('error', $decoded);
        $this->assertSame($message, $decoded['error']['message']);
        $this->assertSame(HttpStatus::BAD_REQUEST, $decoded['error']['code']);
        $this->assertArrayHasKey('timestamp', $decoded);

        $this->assertSame(HttpStatus::BAD_REQUEST, self::$responseCode);
    }

    public function testHandleOptionsOnlySetsHttpOK(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'OPTIONS';

        ob_start();
        $this->controller->handleOptions();
        ob_end_clean();

        $this->assertSame(HttpStatus::OK, self::$responseCode);
        // No additional headers should be set by default
        $this->assertEmpty(self::$headers);
    }

    public function testValidateParamsReturnsNullWhenValid(): void
    {
        $params = ['name' => 'Alice', 'count' => 5];
        $rules = [
            'name' => ['required' => true],
            'count' => ['type' => 'integer'],
        ];

        $result = $this->controller->exposeValidateParams($params, $rules);
        $this->assertNull($result);
    }

    public function testValidateParamsReturnsErrorsForCountOnly(): void
    {
        $params = ['name' => '', 'count' => 'NaN'];
        $rules = [
            'name' => ['required' => true],
            'count' => ['type' => 'integer'],
        ];

        $result = $this->controller->exposeValidateParams($params, $rules);
        $this->assertIsArray($result);
        $this->assertArrayNotHasKey('name', $result);
        $this->assertArrayHasKey('count', $result);
    }

    public function testGetParamFromGet(): void
    {
        $_GET['param'] = 'value';
        $this->assertSame('value', $this->controller->exposeGetParam('param', 'default', 'get'));
    }

    public function testGetParamFromPost(): void
    {
        $_POST['param'] = 'value2';
        $this->assertSame('value2', $this->controller->exposeGetParam('param', 'default', 'post'));
    }

    public function testGetParamDefault(): void
    {
        $this->assertSame('default', $this->controller->exposeGetParam('nonexistent', 'default', 'any'));
    }
}
