<?php

namespace App\Utils;

class Server
{
    /**
     * Obtém o IP do cliente
     *
     * @return string
     */
    public static function getClientIp(): string
    {
        // Verifica headers de proxy
        $headers = [
            'HTTP_X_FORWARDED_FOR',
            'HTTP_X_REAL_IP',
            'HTTP_CLIENT_IP',
            'REMOTE_ADDR'
        ];
        $flags = FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE;

        foreach ($headers as $header) {
            $value = $_SERVER[$header] ?? null;
            if (!$value) {
                continue;
            }

            // Handle comma-separated IPs (X-Forwarded-For)
            foreach (explode(',', $value) as $candidate) {
                $candidate = trim($candidate);
                if (filter_var($candidate, FILTER_VALIDATE_IP, $flags)) {
                    return $candidate;
                }
            }
        }

        return $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
    }
}