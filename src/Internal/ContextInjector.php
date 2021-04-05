<?php

namespace Empress\Internal;

use Amp\Http\Server\Request;
use Amp\Http\Server\Response;
use Amp\Promise;
use Empress\Context;
use Throwable;
use function Amp\call;

/**
 * Class ContextInjector.
 *
 * Used for injecting the context object into handlers.
 * The context is injected in such a way that alleviates the need for manually returning a response from handlers.
 *
 * @package Empress\Internal
 * @internal
 */
class ContextInjector
{
    private Context $context;

    private ?Throwable $exception;

    /**
     * ContextInjector constructor.
     *
     * @param Context $context
     * @param Throwable|null $exception
     */
    public function __construct(Context $context, ?Throwable $exception = null)
    {
        $this->context = $context;
        $this->exception = $exception;
    }

    /**
     * Injects the context object into the handler.
     * It runs the handler and returns a promise that will eventually resolve to a response.
     *
     * @param callable $handler
     * @return Promise<Response>
     */
    public function inject(callable $handler): Promise
    {
        return call($handler, $this->context, $this->exception);
    }

    public function getRequest(): Request
    {
        return $this->context->getHttpServerRequest();
    }

    public function getResponse(): Response
    {
        return $this->context->getHttpServerResponse();
    }

    public function getThrowable(): ?Throwable
    {
        return $this->exception;
    }

    public function setThrowable(Throwable $exception): void
    {
        $this->exception = $exception;
    }
}
