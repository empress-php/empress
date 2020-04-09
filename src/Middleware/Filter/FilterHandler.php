<?php

namespace Empress\Middleware\Filter;

use Empress\Middleware\MiddlewareHandlerInterface;

class FilterHandler implements MiddlewareHandlerInterface
{

    /**
     * @var callable
     */
    private $callable;

    /**
     * FilterHandler constructor.
     * @param callable $callable
     */
    public function __construct(callable $callable)
    {
        $this->callable = $callable;
    }

    public function getCallable(): callable
    {
        return $this->callable;
    }
}
