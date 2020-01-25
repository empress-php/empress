<?php

namespace Empress\Internal;

use Amp\Http\Server\Request as HttpRequest;
use Amp\Http\Server\RequestHandler as RequestHandlerInterface;
use Amp\Promise;
use Empress\Transformer\DefaultTransformer;
use Empress\Transformer\ResponseTransformerInterface;

use function Amp\call;

/**
 * Empress-tailored request handler that handles response transformers.
 */
final class RequestHandler implements RequestHandlerInterface
{
    /** @var callable */
    private $handler;

    /** @var ResponseTransformerInterface|null */
    private $responseTransformer;

    public function __construct(callable $handler, ResponseTransformerInterface $responseTransformer = null)
    {
        $this->handler = $handler;
        $this->responseTransformer = $responseTransformer ?? new DefaultTransformer();
    }

    /**
     * @inheritdoc
     */
    public function handleRequest(HttpRequest $request): Promise
    {
        $promise = call($this->handler, new Request($request));

        return $this->responseTransformer->transform($promise);
    }
}
