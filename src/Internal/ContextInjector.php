<?php

namespace Empress\Internal;

use Amp\Deferred;
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
 * The context is injected in such a way that alleviates the need for manually returning a response object from a request handler.
 *
 * @package Empress\Internal
 */
class ContextInjector
{

    /**
     * @var callable
     */
    private $handler;

    /**
     * @var Request
     */
    private $request;

    /**
     * @var Response
     */
    private $response;

    /**
     * @var Throwable
     */
    private $exception;

    /**
     * ContextInjector constructor.
     *
     * @param callable $handler
     * @param Request $request
     * @param Response|null $response
     * @param Throwable|null $exception
     */
    public function __construct(callable $handler, Request $request, Response $response = null, Throwable $exception = null)
    {
        $this->handler = $handler;
        $this->request = $request;
        $this->response = $response ?? new Response();
        $this->exception = $exception;
    }

    /**
     * Injects the context object into the handler.
     * It runs the handler and returns a promise that will eventually resolve to a response.
     *
     * @return Promise<Response>
     */
    public function inject(): Promise
    {
        $context = new Context($this->request, $this->response, $this->exception);
        $deferred = new Deferred();

        call($this->handler, $context)->onResolve(function (?Throwable $t) use ($deferred) {
            if (!\is_null($t)) {
                $deferred->fail($t);

                return;
            }

            $deferred->resolve($this->response);
        });

        return $deferred->promise();
    }
}
