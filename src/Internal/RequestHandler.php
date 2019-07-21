<?php

namespace Empress\Internal;

use Amp\Http\Server\Request;
use Amp\Http\Server\RequestHandler as RequestHandlerInterface;
use Amp\Http\Server\Response;
use Amp\Http\Server\Router;
use Amp\Promise;
use function Amp\call;

final class RequestHandler implements RequestHandlerInterface
{
    /** @var callable */
    private $callable;


    public function __construct(callable $callable)
    {
        $this->callable = $callable;
    }

    /**
     * {@inheritdoc}
     */
    public function handleRequest(Request $request): Promise
    {
        $params = $request->getAttribute(Router::class);
        $response = new Response;

        return call($this->callable, $request, $response, $params);
    }
}
