<?php

declare(strict_types=1);

namespace Empress\Routing\Handler;

use Empress\Routing\Path\Path;
use Empress\Routing\Path\PathMatcher;
use Empress\Routing\Path\RegexBuilder;

final class HandlerEntry
{
    private int $type;

    private Path $path;

    /**
     * @var callable
     */
    private $handler;

    private PathMatcher $pathMatcher;

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

    public function getPath(): Path
    {
        return $this->path;
    }

    public function setPath(Path $path): void
    {
        $this->path = $path;
    }

    public function getHandler(): callable
    {
        return $this->handler;
    }

    public function getPathMatcher(): PathMatcher
    {
        return $this->pathMatcher;
    }
}
