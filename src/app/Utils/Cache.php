<?php

namespace App\Utils;

class Cache
{
    private static string $cacheDir;
    private static int $defaultTtl = 300; // 5 minutes

    /**
     * Initialize cache directory
     */
    public static function initialize(): void
    {
        self::$cacheDir = sys_get_temp_dir() . '/movement_ranking_cache';

        self::ensureCacheDir();

//        if (!is_dir(self::$cacheDir)) {
//            mkdir(self::$cacheDir, 0755, true);
//        }
    }

    private static function ensureCacheDir(): void
    {
        $cacheDir = self::$cacheDir;

        if (!is_dir($cacheDir)) {
            if (false === @mkdir($cacheDir, 0777, true) && !is_dir($cacheDir)) {
                throw new \RuntimeException("Não foi possível criar o diretório de cache em: {$cacheDir}");
            }
        }

        if (!is_writable($cacheDir)) {
            @chmod($cacheDir, 0777);
        }
    }

    /**
     * Get cache key file path
     * 
     * @param string $key
     * @return string
     */
    private static function getCacheFilePath(string $key): string
    {
        return self::$cacheDir . '/' . md5($key) . '.cache';
    }

    /**
     * Set cache value
     * 
     * @param string $key
     * @param mixed $value
     * @param int|null $ttl Time to live in seconds
     * @return bool
     */
    public static function set(string $key, mixed $value, ?int $ttl = null): bool
    {
        self::initialize();
        
        $ttl = $ttl ?? self::$defaultTtl;
        $expiry = time() + $ttl;
        
        $data = [
            'value' => $value,
            'expiry' => $expiry
        ];

        $filePath = self::getCacheFilePath($key);
        return file_put_contents($filePath, serialize($data)) !== false;
    }

    /**
     * Get cache value
     * 
     * @param string $key
     * @return mixed|null
     */
    public static function get(string $key): mixed
    {
        self::initialize();
        
        $filePath = self::getCacheFilePath($key);
        
        if (!file_exists($filePath)) {
            return null;
        }

        $data = unserialize(file_get_contents($filePath));
        
        if (!$data || !isset($data['expiry']) || time() > $data['expiry']) {
            self::delete($key);
            return null;
        }

        return $data['value'];
    }

    /**
     * Delete cache value
     * 
     * @param string $key
     * @return bool
     */
    public static function delete(string $key): bool
    {
        self::initialize();
        
        $filePath = self::getCacheFilePath($key);
        
        if (file_exists($filePath)) {
            return unlink($filePath);
        }

        return true;
    }

    /**
     * Check if cache key exists and is valid
     * 
     * @param string $key
     * @return bool
     */
    public static function has(string $key): bool
    {
        return self::get($key) !== null;
    }

    /**
     * Clear all cache
     * 
     * @return bool
     */
    public static function clear(): bool
    {
        self::initialize();
        
        $files = glob(self::$cacheDir . '/*.cache');
        
        foreach ($files as $file) {
            if (!unlink($file)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Get or set cache value with callback
     * 
     * @param string $key
     * @param callable $callback
     * @param int|null $ttl
     * @return mixed
     */
    public static function remember(string $key, callable $callback, ?int $ttl = null): mixed
    {
        $value = self::get($key);
        
        if ($value !== null) {
            return $value;
        }

        $value = $callback();
        self::set($key, $value, $ttl);
        
        return $value;
    }
}

