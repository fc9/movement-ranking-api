<?php

namespace App\Providers;

/**
 * Gerenciador de Providers
 *
 * Responsável por registrar e executar providers na ordem correta
 */
class ProviderManager
{
    private array $providers = [];
    private bool $sorted = false;

    /**
     * Registra um provider
     *
     * @param ProviderInterface $provider
     */
    public function register(ProviderInterface $provider): void
    {
        $this->providers[] = $provider;
        $this->sorted = false;
    }

    /**
     * Executa todos os providers registrados
     *
     * @param array $request Dados da requisição
     * @return array|null Retorna resposta se algum provider interromper, null caso contrário
     */
    public function execute(array $request): ?array
    {
        $this->sortProviders();

        foreach ($this->providers as $provider) {
            if (!$provider->shouldHandle($request)) {
                continue;
            }

            $response = $provider->handle($request);

            if ($response !== null) {
                return $response;
            }
        }

        return null; // Todos os providers passaram, continua para o roteamento
    }

    /**
     * Ordena providers por prioridade
     */
    private function sortProviders(): void
    {
        if ($this->sorted) {
            return;
        }

        usort($this->providers, function(ProviderInterface $a, ProviderInterface $b) {
            return $a->getPriority() <=> $b->getPriority();
        });

        $this->sorted = true;
    }

    /**
     * Obtém lista de providers registrados
     *
     * @return array
     */
    public function getProviders(): array
    {
        $this->sortProviders();
        return $this->providers;
    }

    /**
     * Remove todos os providers
     */
    public function clear(): void
    {
        $this->providers = [];
        $this->sorted = false;
    }

    /**
     * Obtém informações sobre os providers registrados
     *
     * @return array
     */
    public function getProvidersInfo(): array
    {
        $this->sortProviders();

        return array_map(function(ProviderInterface $provider) {
            return [
                'name' => $provider->getName(),
                'priority' => $provider->getPriority(),
                'class' => get_class($provider)
            ];
        }, $this->providers);
    }
}

