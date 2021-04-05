<?php

namespace Empress\Routing\Exception;

class ExceptionHandler
{

    /**
     * @var string
     */
    private $exceptionClass;

    /**
     * @var callable
     */
    private $callable;


    /**
     * ExceptionMappingMiddlewareHandler constructor.
     * @param callable $callable
     * @param string $exceptionClass
     */
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

    /**
     * @return string
     */
    public function getExceptionClass(): string
    {
        return $this->exceptionClass;
    }
}