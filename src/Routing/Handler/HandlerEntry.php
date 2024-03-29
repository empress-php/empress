<?php

declare(strict_types=1);

namespace Empress\Routing\Handler;

use Empress\Routing\Path\Path;
use Empress\Routing\Path\PathMatcher;
use Empress\Routing\Path\RegexBuilder;

final class HandlerEntry
{
    private PathMatcher $pathMatcher;

    /**
     * @param callable $handler
     */
    public function __construct(
        private HandlerTypeEnum $type,
        private Path $path,
        private mixed $handler
    ) {
        $this->pathMatcher = new PathMatcher((new RegexBuilder())->buildRegex($this->path));
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
