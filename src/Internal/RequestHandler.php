<?php

namespace Empress\Internal;

use Amp\Http\Server\Request;
use Amp\Http\Server\RequestHandler as RequestHandlerInterface;
use Amp\Http\Server\Router;
use Amp\Http\Server\Session\Session;
use Amp\Http\Server\Session\SessionMiddleware;
use Amp\Promise;
use Empress\ResponseTransformerInterface;

use function Amp\call;
use function Amp\Promise\wait;

final class RequestHandler implements RequestHandlerInterface
{
    /** @var callable */
    private $handler;

    /** @var \Empress\ResponseTransformerInterface|null */
    private $responseTransformer;

    public function __construct(callable $handler, ResponseTransformerInterface $responseTransformer = null)
    {
        $this->handler = $handler;
        $this->responseTransformer = $responseTransformer;
    }

    /**
     * {@inheritdoc}
     */
    public function handleRequest(Request $request): Promise
    {
        $params = $request->getAttribute(Router::class);
        $promise = call($this->handler, $params, $request);

        if (!\is_null($this->responseTransformer)) {
            $promise = $this->responseTransformer->transform($promise);
        }

        return $promise;
    }
}
