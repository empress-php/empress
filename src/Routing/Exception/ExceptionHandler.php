<?php

declare(strict_types=1);

namespace Empress\Routing\Exception;

final class ExceptionHandler
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
    
    public function getCallable(): callable
    {
        return $this->callable;
    }

    public function getExceptionClass(): string
    {
        return $this->exceptionClass;
    }
}
