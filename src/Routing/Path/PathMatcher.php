<?php

declare(strict_types=1);

namespace Empress\Routing\Path;

use cash\LRUCache;

final class PathMatcher
{
    private const CACHE_SIZE = 512;

    private LRUCache $cache;

    public function __construct(private string $regex)
    {
        $this->cache = new LRUCache(self::CACHE_SIZE);
    }

    public function matches(string $toMatch): bool
    {
        if ($this->cache->containsKey($toMatch)) {
            return $this->cache->get($toMatch);
        }

        $result = \preg_match($this->regex, $toMatch) === 1;

        $this->cache->put($toMatch, $result);

        return $result;
    }

    public function extractNamedParams(string $toMatch): array
    {
        $cacheKey = $toMatch . '_PARAMS';

        if ($this->cache->containsKey($cacheKey)) {
            return $this->cache->get($cacheKey);
        }

        \preg_match($this->regex, $toMatch, $matches);

        $params = \array_filter($matches, fn (mixed $key) => \is_string($key), \ARRAY_FILTER_USE_KEY);

        $this->cache->put($cacheKey, $params);

        return $params;
    }

    public function extractWildcards(string $toMatch): array
    {
        $cacheKey = $toMatch . '_WILDCARDS';

        if ($this->cache->containsKey($cacheKey)) {
            return $this->cache->get($cacheKey);
        }

        \preg_match($this->regex, $toMatch, $matches);

        $wildcards = \array_values(
            \array_filter(
                \array_splice($matches, 1),
                fn (mixed $key) => \is_int($key),
                \ARRAY_FILTER_USE_KEY
            )
        );

        $this->cache->put($cacheKey, $wildcards);

        return $wildcards;
    }
}
