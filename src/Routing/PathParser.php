<?php

namespace Empress\Routing;

use cash\LRUCache;

class PathParser
{
    private const CACHE_SIZE = 512;

    private string $regex;

    private LRUCache $cache;

    /**
     * PathParser constructor.
     *
     * @param Path $path
     */
    public function __construct(Path $path)
    {
        $this->regex = $this->createRegex($path);
        $this->cache = new LRUCache(self::CACHE_SIZE);
    }

    public function match(string $toMatch): ?array
    {
        if (($matches = $this->cache->get($toMatch)) === null) {
            $result = preg_match($this->regex, $toMatch, $matches);
            if ($result !== 1) {
                $this->cache->put($toMatch, null);

                return null;
            }

            // If there is more than one match, the first array element will be the whole string matched.
            // With one match that's ok. Otherwise, only matched groups are important.
            $matches = count($matches) === 1 ? $matches : array_slice($matches, 1);
            $this->cache->put($toMatch, $matches);
        }

        return $matches;
    }

    private function createRegex(Path $path): string
    {
        $segments = $path->toSegments();

        return '#^/' . rtrim(implode('/', array_map(function (string $segment): string {
            if ($segment === '*') {
                return '([^/]+)';
            }

            if ($segment[0] === ':') {
                $paramName = preg_quote(substr($segment, 1), '/');

                return '(?<' . $paramName . '>[^/]+)';
            }

            return '(' . preg_quote($segment, '/') . ')';
        }, $segments)), '/') . '$#';
    }
}
