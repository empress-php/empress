<?php

namespace Empress\Middleware;

use Amp\Http\Server\Middleware;

/**
 * MultiMiddlewareInterface.
 *
 * This type of middleware aggregates handlers that are added to it.
 * The handlers are iterated over and the middleware handler can decide if some of them are to be fired or not.
 * This type of handler is useful when one wants to process many handlers that essentially provide the same kind of functionality.
 * In fact, this helps to alleviate the need to stack many filtering middleware on top of each other.
 * In theory, aggregating middleware could be done by using a pattern like this:
 *
 * `filter_middleware1 > filter_middleware2 > filter_middlewareN > other_middleware`
 *
 * However, with a more efficient implementation it should be possible to do it like this:
 *
 * `filter_middleware(handler1, handler2, handler3) > other_middleware`
 *
 * @package Empress\Middleware
 */
interface AggregateMiddlewareInterface extends Middleware
{

    /**
     * Adds a handler to the internal middleware chain of the aggregate middleware.
     * Child classes can further specialize what kind of handlers they can consume.
     *
     * @param MiddlewareHandlerInterface $handler
     */
    public function addHandler($handler): void;
}
