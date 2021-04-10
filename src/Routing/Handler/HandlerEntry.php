<?php

namespace Empress\Routing\Handler;

use Empress\Routing\Path\Path;
use Empress\Routing\Path\PathMatcher;
use Empress\Routing\Path\RegexBuilder;

class HandlerEntry
{

    /**
     * @var int
     */
    private $type;

    /**
     * @var Path
     */
    private $path;

    /**
     * @var callable
     */
    private $handler;

    /**
     * @var PathMatcher
     */
    private $pathMatcher;

    /**
     * HandlerEntry constructor.
     *
     * @param int $type
     * @param Path $path
     * @param callable $handler
     */
    public function __construct(int $type, Path $path, callable $handler)
    {
        $this->type = $type;
        $this->path = $path;
        $this->handler = $handler;
        $this->pathMatcher = new PathMatcher(new RegexBuilder($path));
    }

    public function getType(): int
    {
        return $this->type;
    }

    /**
     * @return Path
     */
    public function getPath(): Path
    {
        return $this->path;
    }

    public function setPath(Path $path): void
    {
        $this->path = $path;
    }

    /**
     * @return callable
     */
    public function getHandler(): callable
    {
        return $this->handler;
    }

    public function getPathMatcher(): PathMatcher
    {
        return $this->pathMatcher;
    }
}
