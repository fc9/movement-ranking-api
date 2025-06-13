<?php

namespace App\Providers;

/**
 * Classe abstrata base para Providers
 *
 * Fornece implementação padrão para métodos comuns
 */
abstract class Provider implements ProviderInterface
{
    protected int $priority = 100;
    protected string $name = '';

    public function __construct()
    {
        if (empty($this->name)) {
            $this->name = static::class;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getPriority(): int
    {
        return $this->priority;
    }

    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * {@inheritdoc}
     */
    public function shouldHandle(array $request): bool
    {
        // Por padrão, todos os providers são executados
        return true;
    }

    /**
     * Cria uma resposta de erro padronizada
     *
     * @param int $code Código HTTP
     * @param string $message Mensagem de erro
     * @param array $extra Dados extras
     * @return array
     */
    protected function createErrorResponse(int $code, string $message, array $extra = []): array
    {
        return [
            'success'   => false,
            'error'     => array_merge([
                'code'    => $code,
                'message' => $message
            ], $extra),
            'timestamp' => date('c')
        ];
    }

    /**
     * Registra log do provider
     *
     * @param string $level Nível do log (info, warning, error)
     * @param string $message Mensagem
     * @param array $context Contexto adicional
     */
    protected function log(string $level, string $message, array $context = []): void
    {
        $logMessage = sprintf(
            "[%s] [%s] %s: %s %s",
            date('Y-m-d H:i:s'),
            strtoupper($level),
            $this->getName(),
            $message,
            !empty($context) ? json_encode($context) : ''
        );

        error_log($logMessage);
    }
}
