<?php

namespace App\Providers;

/**
 * Provider de Debug/Informações
 *
 * Adiciona informações de debug quando em modo desenvolvimento
 */
class DebugProvider extends Provider
{
    protected int $priority = 999; // Baixa prioridade - executa por último
    protected string $name = 'Debug';

    /**
     * {@inheritdoc}
     */
    public function handle(array $request): ?array
    {
        // Adiciona header com informações de debug
        if ($_ENV['APP_ENV'] === 'development') {
            header('X-Debug-Providers: ' . $this->getProvidersExecuted());
            header('X-Debug-Request-Time: ' . date('c'));
        }

        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function shouldHandle(array $request): bool
    {
        return $_ENV['APP_ENV'] === 'development';
    }

    /**
     * Obtém lista de providers executados
     *
     * @return string
     */
    private function getProvidersExecuted(): string
    {
        // Esta é uma implementação simplificada
        // Em uma implementação real, você poderia injetar o ProviderManager
        return 'SecurityHeaders,RateLimit,RequestLog,Debug';
    }
}

