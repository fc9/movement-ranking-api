<?php

namespace App\Utils;

class Validator
{
    /**
     * Sanitize string input
     * 
     * @param string $input
     * @return string
     */
    public static function sanitizeString(string $input): string
    {
        // Remove null bytes
        $input = str_replace("\0", '', $input);
        
        // Trim whitespace
        $input = trim($input);
        
        // Remove control characters except tab, newline, and carriage return
        $input = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', '', $input);
        
        return $input;
    }

    /**
     * Validate movement identifier
     * 
     * @param string $identifier
     * @return bool
     */
    public static function isValidMovementIdentifier(string $identifier): bool
    {
        $identifier = self::sanitizeString($identifier);
        
        // Check if identifier is not empty
        if (empty($identifier)) {
            return false;
        }

        // Check length (max 255 characters)
        if (strlen($identifier) > 255) {
            return false;
        }

        // If numeric, check if it's a positive integer
        if (is_numeric($identifier)) {
            $numericValue = (int) $identifier;
            return $numericValue > 0 && $numericValue <= PHP_INT_MAX;
        }

        // If string, check if it contains only valid characters
        // Allow letters, numbers, spaces, hyphens, underscores
        return preg_match('/^[a-zA-Z0-9\s\-_]+$/', $identifier) === 1;
    }

    /**
     * Validate and sanitize movement identifier
     * 
     * @param string $identifier
     * @return string|null Returns sanitized identifier or null if invalid
     */
    public static function validateAndSanitizeMovementIdentifier(string $identifier): ?string
    {
        $sanitized = self::sanitizeString($identifier);
        
        if (self::isValidMovementIdentifier($sanitized)) {
            return $sanitized;
        }
        
        return null;
    }

    /**
     * Check for SQL injection patterns
     * 
     * @param string $input
     * @return bool
     */
    public static function containsSqlInjectionPatterns(string $input): bool
    {
        $patterns = [
            '/(\b(SELECT|INSERT|UPDATE|DELETE|DROP|CREATE|ALTER|EXEC|UNION|SCRIPT)\b)/i',
            '/(\-\-|\#|\/\*|\*\/)/i',
            '/(\'|\"|\;|\||\&|\$)/i'
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $input)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Rate limiting check (simple implementation)
     * 
     * @param string $clientIp
     * @param int $maxRequests
     * @param int $timeWindow
     * @param string|null $cacheDir
     * @return bool
     */
    public static function checkRateLimit(
        string $clientIp,
        int $maxRequests = 100,
        int $timeWindow = 3600,
        string $cacheDir = null): bool
    {

        $cacheDir = $cacheDir ?: sys_get_temp_dir();
        $cacheFile = rtrim($cacheDir, '/\\')  . '/rate_limit_' . md5($clientIp);
        
        if (!file_exists($cacheFile)) {
            file_put_contents($cacheFile, json_encode(['count' => 1, 'timestamp' => time()]));
            return true;
        }

        $data = json_decode(file_get_contents($cacheFile), true);
        
        // Reset if time window has passed
        if (time() - $data['timestamp'] > $timeWindow) {
            file_put_contents($cacheFile, json_encode(['count' => 1, 'timestamp' => time()]));
            return true;
        }

        // Check if limit exceeded
        if ($data['count'] >= $maxRequests) {
            return false;
        }

        // Increment counter
        $data['count']++;
        file_put_contents($cacheFile, json_encode($data));
        
        return true;
    }
}

