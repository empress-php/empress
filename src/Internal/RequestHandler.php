<?php

namespace Empress\Internal;

use Amp\Http\Server\Request;
use Amp\Http\Server\RequestHandler as RequestHandlerInterface;
use Amp\Http\Server\Router;
use Amp\Promise;
use Empress\ResponseTransformerInterface;

use function Amp\call;

/**
 * Empress-tailored request handler that injects the param array
 * and handles response transformers.
 */
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
