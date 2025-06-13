<?php

namespace App\Providers;

/**
 * Interface para Providers da aplicação
 *
 * Providers são executados antes do roteamento e permitem
 * adicionar lógicas transversais como segurança, validação,
 * autenticação, logging, etc.
 */
interface ProviderInterface
{
    /**
     * Executa a lógica do provider
     *
     * @param array $request Dados da requisição
     * @return array|null Retorna null para continuar ou array com resposta para interromper
     */
    public function handle(array $request): ?array;

    /**
     * Retorna a prioridade do provider (menor número = maior prioridade)
     *
     * @return int
     */
    public function getPriority(): int;

    /**
     * Retorna o nome do provider
     *
     * @return string
     */
    public function getName(): string;

    /**
     * Verifica se o provider deve ser executado para a requisição atual
     *
     * @param array $request Dados da requisição
     * @return bool
     */
    public function shouldHandle(array $request): bool;
}

