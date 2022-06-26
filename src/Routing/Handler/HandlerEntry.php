<?php

declare(strict_types=1);

namespace Empress\Routing\Handler;

use Empress\Routing\Path\Path;
use Empress\Routing\Path\PathMatcher;
use Empress\Routing\Path\RegexBuilder;

final class HandlerEntry
{
    private PathMatcher $pathMatcher;

    public function __construct(
        private HandlerTypeEnum $type,
        private Path $path,
        private $handler
    ) {
        $this->pathMatcher = new PathMatcher(new RegexBuilder($path));
    }

    public function getType(): HandlerTypeEnum
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
