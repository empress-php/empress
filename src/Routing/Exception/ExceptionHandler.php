<?php

namespace Empress\Routing\Exception;

class ExceptionHandler
{
    private string $exceptionClass;

    /**
     * @var callable
     */
    private $callable;

    public function __construct(callable $callable, string $exceptionClass)
    {
        $this->exceptionClass = $exceptionClass;
        $this->callable = $callable;
    }

    /**
     * @return callable
     */
    public function getCallable(): callable
    {
        return $this->callable;
    }

    public function getExceptionClass(): string
    {
        return $this->exceptionClass;
    }
}
