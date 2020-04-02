<?php

namespace Empress\Internal;

use Amp\Http\Server\Request;
use Amp\Http\Server\RequestHandler as RequestHandlerInterface;
use Amp\Promise;
use function Amp\call;

/**
 * Empress-tailored request handler that handles response transformers.
 */
final class RequestHandler implements RequestHandlerInterface
{
    use HaltAwareTrait;

    /**
     * @var callable
     */
    private $handler;

    public function __construct(callable $handler)
    {
        $this->handler = $handler;
    }

    /**
     * @inheritdoc
     */
    public function handleRequest(Request $request): Promise
    {
        return call(function () use ($request) {
            $injector = new ContextInjector($this->handler, $request);

            return yield $this->resolveInjectionResult($injector);
        });
    }
}
