<?php

namespace Tests\Unit\Utils;

use App\Utils\Server;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;

class ServerTest extends TestCase
{

    public function testReturnsIpFromXForwardedFor()
    {
        $_SERVER['HTTP_X_FORWARDED_FOR'] = '192.0.2.1, 198.51.100.1';

        $ip = Server::getClientIp();

        $this->assertEquals('192.0.2.1', $ip);
    }

    public function testSkipsInvalidPrivateIps()
    {
        $_SERVER['HTTP_X_FORWARDED_FOR'] = '10.0.0.1, 192.168.0.1';
        $_SERVER['HTTP_X_REAL_IP']       = '203.0.113.5';

        $ip = Server::getClientIp();

        $this->assertEquals('203.0.113.5', $ip);
    }

    public function testReturnsRemoteAddrAsFallback()
    {
        $_SERVER['HTTP_X_FORWARDED_FOR'] = '';
        $_SERVER['HTTP_X_REAL_IP']       = '';
        $_SERVER['REMOTE_ADDR']          = '198.51.100.2';

        $ip = Server::getClientIp();

        $this->assertEquals('198.51.100.2', $ip);
    }

    public function testReturnsDefaultWhenNoServerData()
    {
        $_SERVER = [];
        $ip      = Server::getClientIp();

        $this->assertEquals('127.0.0.1', $ip);
    }

    private function resetClientIpCache(): void
    {
        // Força reset de cache do método com `static $ip` usando Reflection
        $method = new ReflectionMethod(Server::class, 'getClientIp');
//        $method->setAccessible(true);
        $staticVariables_ = $method->getStaticVariables();
        $staticVariables  = &$staticVariables_;
        if (isset($staticVariables['ip'])) {
            $staticVariables['ip'] = null;
        }
    }
}