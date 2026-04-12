<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;

class CacheService
{
    /**
     * Get or set cached data with automatic cache invalidation support.
     *
     * @param string $key Cache key
     * @param callable $callback Function to generate cache miss data
     * @param int $ttl Time to live in seconds (default: 1 hour)
     * @param array $tags Cache tags for granular invalidation
     * @return mixed
     */
    public static function remember(string $key, callable $callback, int $ttl = 3600, array $tags = []): mixed
    {
        if (config('cache.default') === 'redis' && !empty($tags)) {
            return Cache::tags($tags)->remember($key, $ttl, $callback);
        }

        return Cache::remember($key, $ttl, $callback);
    }

    /**
     * Invalidate cache by tags.
     *
     * @param array $tags Cache tags to invalidate
     * @return bool
     */
    public static function invalidateTags(array $tags): bool
    {
        if (config('cache.default') === 'redis') {
            Cache::tags($tags)->flush();
            return true;
        }

        return false;
    }

    /**
     * Build cache key with prefix to avoid collisions.
     *
     * @param string $prefix
     * @param mixed ...$parts
     * @return string
     */
    public static function key(string $prefix, ...$parts): string
    {
        return 'unione:' . $prefix . ':' . implode(':', array_map(function ($part) {
            return is_null($part) ? 'null' : $part;
        }, $parts));
    }

    /**
     * Cache for organization hierarchy (universities, faculties, departments).
     */
    const TAG_ORGANIZATION = 'organization';

    /**
     * Cache for course catalog and sections.
     */
    const TAG_ACADEMIC = 'academic';

    /**
     * Cache for user-specific data (profiles, schedules).
     */
    const TAG_USER = 'user';

    /**
     * Cache for analytics and statistics.
     */
    const TAG_ANALYTICS = 'analytics';

    /**
     * Cache for configuration data.
     */
    const TAG_CONFIG = 'config';
}
