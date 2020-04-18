<?php

namespace Empress\Routing;

use Empress\Exception\RouteException;
use cash\LRUCache;

class PathParser
{
    private const CACHE_SIZE = 512;

    /**
     * @var string
     */
    private $path;

    /**
     * @var string
     */
    private $regex;

    /**
     * @var LRUCache
     */
    private $cache;

    /**
     * PathParser constructor.
     *
     * @param string $path
     * @throws RouteException
     */
    public function __construct(string $path)
    {
        $this->path = $path;
        $segments = $this->toSegments($path);
        $this->regex = $this->toRegex($segments);
        $this->cache = new LRUCache(self::CACHE_SIZE);

        var_dump($this->regex);
    }

    public function match(string $toMatch)
    {
        if (($matches = $this->cache->get($toMatch)) === null) {
            $result = preg_match($this->regex, $toMatch, $matches);
            if ($result !== 1) {
                $this->cache->put($toMatch, null);

                return null;
            }

            // If there is more than one match, the first array element will be the whole string matched.
            // With one match that's ok. Otherwise, only matched groups are of any interest.
            $matches = count($matches) === 1 ? $matches : array_slice($matches, 1);
            $this->cache->put($toMatch, $matches);
        }

        return $matches;
    }

    /**
     * @param string $path
     * @return array
     * @throws RouteException
     */
    private function toSegments(string $path): array
    {
        $segments = array_filter(explode('/', $path));

        foreach ($segments as $segment) {
            if (strlen($segment) === 1 && $segment === ':') {
                throw new RouteException('Invalid empty segment in: ' . $path);
            }
        }

        return $segments;
    }

    private function toRegex(array $segments): string
    {
        return '/^\/' . rtrim(implode('\/', array_map(function (string $segment) {
            if ($segment === '*') {
                return '([^\/]+)';
            } elseif ($segment[0] === ':') {
                $paramName = preg_quote(substr($segment, 1), '/');

                return '(?<' . $paramName . '>[^\/]+)';
            } else {
                return '(' . preg_quote($segment, '/') . ')';
            }
        }, $segments)), '/') . '$/';
    }
}
