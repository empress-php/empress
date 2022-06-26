<?php

declare(strict_types=1);

namespace Empress\Routing\Status;

use Amp\Http\Server\Request;

final class StatusHandler
{
    private int $status;

    private array $headers;

    /**
     * @var callable
     */
    private $callable;

    public function __construct(callable $callable, int $status, array $headers = [])
    {
        $this->callable = $callable;
        $this->status = $status;
        $this->headers = $headers;
    }

    public function satisfiesHeaders(Request $request): bool
    {
        return \array_reduce(\array_keys($this->headers), fn (bool $acc, mixed $key) => $acc && ($request->getHeader($key) === $this->headers[$key]), true);
    }

    public function getStatus(): int
    {
        return $this->status;
    }

    public function getHeaders(): array
    {
        return $this->headers;
    }

    public function hasHeaders(): bool
    {
        return \count($this->headers) > 0;
    }

    public function getCallable(): callable
    {
        return $this->callable;
    }
}
