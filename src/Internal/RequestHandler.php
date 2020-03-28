<?php

namespace Empress\Internal;

use Amp\Http\Server\Request;
use Amp\Http\Server\RequestHandler as RequestHandlerInterface;
use Amp\Http\Server\Response;
use Amp\Promise;


/**
 * Empress-tailored request handler that handles response transformers.
 */
final class RequestHandler implements RequestHandlerInterface
{
    /** @var callable */
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
        $injector = new ContextInjector($this->handler, $request);

        return $injector->inject();
    }
}
