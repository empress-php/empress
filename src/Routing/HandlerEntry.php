<?php

namespace Empress\Routing;

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
}
