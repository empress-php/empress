<?php

namespace Empress\Middleware\Status;

use Amp\Http\Server\Request;
use Empress\Middleware\MiddlewareHandlerInterface;

/**
 * Class StatusHandler
 * @package Empress\Internal
 */
class StatusHandler implements MiddlewareHandlerInterface
{

    /**
     * @var int
     */
    private $status;

    /**
     * @var array
     */
    private $headers;

    /**
     * @var callable
     */
    private $callable;

    /**
     * StatusHandler constructor.
     * @param int $status
     * @param callable $callable
     * @param array $headers
     */
    public function __construct(int $status, callable $callable, array $headers = [])
    {
        $this->status = $status;
        $this->headers = $headers;
        $this->callable = $callable;
    }

    public function satisfiesHeaders(Request $request): bool
    {
        return array_reduce(array_keys($this->headers), function ($acc, $key) use ($request) {
            return $acc && ($request->getHeader($key) === $this->headers[$key]);
        }, true);
    }

    /**
     * @return int
     */
    public function getStatus(): int
    {
        return $this->status;
    }

    /**
     * @return array
     */
    public function getHeaders(): array
    {
        return $this->headers;
    }

    /**
     * @return bool
     */
    public function hasHeaders(): bool
    {
        return count($this->headers) > 0;
    }

    /**
     * @return callable
     */
    public function getCallable(): callable
    {
        return $this->callable;
    }
}