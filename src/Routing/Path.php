<?php

namespace Empress\Routing;

use Empress\Exception\RouteException;

class Path
{

    /**
     * @var string
     */
    private $path;

    public function __construct(string $path)
    {
        $this->path = $path;
    }

    /**
     * @return array
     * @throws RouteException
     */
    public function toSegments(): array
    {
        $segments = \array_filter(\explode('/', $this->path));

        foreach ($segments as $segment) {
            if (\strlen($segment) === 1 && $segment === ':') {
                throw new RouteException('Invalid empty segment in: ' . $this->path);
            }
        }

        return \array_values($segments);
    }

    public function __toString(): string
    {
        return $this->path;
    }
}
