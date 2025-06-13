<?php

namespace App\Providers;

use App\Utils\Server;

/**
 * Provider de Logging de Requisições
 *
 * Registra informações sobre as requisições recebidas
 */
class RequestLogProvider extends Provider
{
    protected int $priority = 30;
    protected string $name = 'RequestLog';

    /**
     * {@inheritdoc}
     */
    public function handle(array $request): ?array
    {
        $logData = [
            'method' => $request['method'] ?? 'UNKNOWN',
            'uri' => $request['uri'] ?? '/',
            'ip' => Server::getClientIp(),
            'user_agent' => $request['headers']['User-Agent'] ?? 'Unknown',
            'timestamp' => date('c')
        ];

        $this->log('info', 'Request received', $logData);

        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function shouldHandle(array $request): bool
    {
        // Log todas as requisições exceto health check para reduzir ruído
        $uri = $request['uri'] ?? '';
        return $uri !== '/health';
    }
}

