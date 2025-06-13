<?php

namespace App\Providers;

use App\Utils\HttpStatus;

/**
 * Provider de Headers de Segurança
 *
 * Adiciona headers de segurança às respostas
 */
class SecurityHeadersProvider extends Provider
{
    protected int $priority = 20;
    protected string $name = 'SecurityHeaders';

    private array $headers = [
        'Access-Control-Allow-Origin' => '*',
        'Access-Control-Allow-Methods' => 'GET, POST, OPTIONS',
        'Access-Control-Allow-Headers' => 'Content-Type, Authorization',
        // Security headers
        'X-Content-Type-Options' => 'nosniff',
        'X-Frame-Options' => 'DENY',
        'X-XSS-Protection' => '1; mode=block',
        'Referrer-Policy' => 'strict-origin-when-cross-origin',
        'Content-Security-Policy' => "default-src 'self'"
    ];

    /**
     * {@inheritdoc}
     */
    public function handle(array $request): ?array
    {
        // Adiciona headers de segurança
        foreach ($this->headers as $name => $value) {
            header("$name: $value");
        }

        // Se for requisição OPTIONS (preflight CORS), retorna resposta vazia
        if (($request['method'] ?? '') === 'OPTIONS') {
            http_response_code(HttpStatus::OK);
            return [
                'success' => true,
                'message' => 'CORS preflight'
            ];
        }

        return null;
    }

    /**
     * Adiciona um header customizado
     *
     * @param string $name
     * @param string $value
     */
    public function addHeader(string $name, string $value): void
    {
        $this->headers[$name] = $value;
    }

    /**
     * Remove um header
     *
     * @param string $name
     */
    public function removeHeader(string $name): void
    {
        unset($this->headers[$name]);
    }
}

