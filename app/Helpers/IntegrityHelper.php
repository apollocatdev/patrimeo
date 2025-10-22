<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Cache;

class IntegrityHelper
{
    private const CACHE_PREFIX = 'data_integrity';
    private const CACHE_TTL = 3600; // 1 hour

    public static function store(int $userId, array $integrityResults): void
    {
        $cacheKey = self::getCacheKey($userId);
        Cache::put($cacheKey, $integrityResults, self::CACHE_TTL);
    }

    public static function get(int $userId): ?array
    {
        $cacheKey = self::getCacheKey($userId);
        return Cache::get($cacheKey);
    }

    public static function clear(int $userId): void
    {
        $cacheKey = self::getCacheKey($userId);
        Cache::forget($cacheKey);
    }

    public static function isValid(int $userId): bool
    {
        $integrityData = self::get($userId);

        if (!$integrityData || !isset($integrityData['checks'])) {
            return true; // Default to valid if no data
        }

        foreach ($integrityData['checks'] as $check) {
            if (isset($check['level']) && $check['level'] === 'alert' && $check['count'] > 0) {
                return false;
            }
        }

        return true;
    }

    private static function getCacheKey(int $userId): string
    {
        return self::CACHE_PREFIX . '_' . $userId;
    }
}
