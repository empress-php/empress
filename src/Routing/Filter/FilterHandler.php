<?php

namespace Empress\Routing\Filter;

class FilterHandler
{

    /**
     * @var callable
     */
    private $callable;

    /**
     * @var string
     */
    private $path;

    /**
     * FilterHandler constructor.
     * @param callable $callable
     * @param string|null $path
     */
    public function __construct(callable $callable, string $path = '*')
    {
        $this->callable = $callable;
        $this->path = $path;
    }

    public function getCallable(): callable
    {
        return $this->callable;
    }

    public function getPath(): string
    {
        return $this->path;
    }
}
