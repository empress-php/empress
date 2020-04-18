<?php

namespace Empress\Routing;

class HandlerEntry
{

    /**
     * @var int
     */
    private $type;

    /**
     * @var string
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
     * @param string $path
     * @param callable $handler
     */
    public function __construct(int $type, string $path, callable $handler)
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
     * @return string
     */
    public function getPath(): string
    {
        return $this->path;
    }

    public function setPath(string $path): void
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
