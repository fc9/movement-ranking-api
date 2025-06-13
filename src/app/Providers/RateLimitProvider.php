<?php

namespace App\Providers;

use App\Utils\HttpStatus;
use App\Utils\Server;

/**
 * Provider de Rate Limiting
 *
 * Controla o número de requisições por IP em um período
 */
class RateLimitProvider extends Provider
{
    protected int $priority = 10; // Alta prioridade
    protected string $name = 'RateLimit';

    private int $maxRequests;
    private int $timeWindow;
    private string $cacheDir;

    public function __construct(int $maxRequests = 100, int $timeWindow = 3600)
    {
        parent::__construct();

        $this->maxRequests = $maxRequests;
        $this->timeWindow = $timeWindow;
        $this->cacheDir = sys_get_temp_dir() . '/rate_limit';

        // Cria diretório de cache se não existir
        if (!is_dir($this->cacheDir)) {
            mkdir($this->cacheDir, 0755, true);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function handle(array $request): ?array
    {
        $clientIp = Server::getClientIp();

        if ($this->isAllowed($clientIp)) {
            $this->recordRequest($clientIp);
            return null;
        }

        $this->log('warning', 'Rate limit exceeded', ['ip' => $clientIp]);

        $message = HttpStatus::getReasonPhrase(HttpStatus::TOO_MANY_REQUESTS);
        return $this->createErrorResponse(
            HttpStatus::TOO_MANY_REQUESTS,
            "$message. Please try again later.",
            [
                'retry_after' => $this->getRetryAfter($clientIp),
                'limit'       => $this->maxRequests,
                'window'      => $this->timeWindow
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function shouldHandle(array $request): bool
    {
        // Não aplica rate limit para health check
        $uri = $request['uri'] ?? '';
        return $uri !== '/health';
    }

    /**
     * Verifica se o IP está dentro do limite
     *
     * @param string $ip
     * @return bool
     */
    private function isAllowed(string $ip): bool
    {
        $requests = $this->getRequests($ip);
        $currentTime = time();

        // Remove requisições antigas
        $requests = array_filter(
            $requests,
            function($timestamp) use ($currentTime) {
                return ($currentTime - $timestamp) < $this->timeWindow;
            }
        );

        return count($requests) < $this->maxRequests;
    }

    /**
     * Registra uma nova requisição
     *
     * @param string $ip
     */
    private function recordRequest(string $ip): void
    {
        $requests = $this->getRequests($ip);
        $currentTime = time();

        // Remove requisições antigas
        $requests = array_filter(
            $requests,
            function($timestamp) use ($currentTime) {
                return ($currentTime - $timestamp) < $this->timeWindow;
            }
        );

        // Adiciona nova requisição
        $requests[] = $currentTime;

        $this->saveRequests($ip, $requests);
    }

    /**
     * Obtém as requisições registradas para um IP
     *
     * @param string $ip
     * @return array
     */
    private function getRequests(string $ip): array
    {
        $filename = $this->getCacheFilename($ip);

        if (!file_exists($filename)) {
            return [];
        }

        $content = file_get_contents($filename);
        $data = json_decode($content, true);

        return $data['requests'] ?? [];
    }

    /**
     * Salva as requisições para um IP
     *
     * @param string $ip
     * @param array $requests
     */
    private function saveRequests(string $ip, array $requests): void
    {
        $filename = $this->getCacheFilename($ip);
        $data = [
            'ip' => $ip,
            'requests' => $requests,
            'updated_at' => time()
        ];

        file_put_contents($filename, json_encode($data));
    }

    /**
     * Obtém o tempo para retry
     *
     * @param string $ip
     * @return int
     */
    private function getRetryAfter(string $ip): int
    {
        $requests = $this->getRequests($ip);

        if (empty($requests)) {
            return 0;
        }

        $oldestRequest = min($requests);
        $retryAfter = $this->timeWindow - (time() - $oldestRequest);

        return max(0, $retryAfter);
    }

    /**
     * Obtém o nome do arquivo de cache para um IP
     *
     * @param string $ip
     * @return string
     */
    private function getCacheFilename(string $ip): string
    {
        $hash = md5($ip);
        return $this->cacheDir . '/rate_limit_' . $hash . '.json';
    }

    /**
     * Limpa cache antigo (método utilitário)
     */
    public function cleanupCache(): void
    {
        $files = glob($this->cacheDir . '/rate_limit_*.json');
        $currentTime = time();

        foreach ($files as $file) {
            $content = file_get_contents($file);
            $data = json_decode($content, true);

            if (isset($data['updated_at']) &&
                ($currentTime - $data['updated_at']) > $this->timeWindow) {
                unlink($file);
            }
        }
    }
}
