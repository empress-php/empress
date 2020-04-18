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
     * @param string $exceptionClass
     * @param callable $callable
     */
    public function __construct(string $exceptionClass, callable $callable)
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