<?php

namespace Empress\Internal;

use Amp\Http\Server\Request;
use Amp\Http\Server\RequestHandler as RequestHandlerInterface;
use Amp\Http\Server\Router;
use Amp\Promise;
use Psr\Container\ContainerInterface;

use function Amp\call;

final class RequestHandler implements RequestHandlerInterface
{
    /** @var \Closure */
    private $closure;

    /** @var Psr\Container\ContainerInterface */
    private $container;

    public function __construct(\Closure $closure, ContainerInterface $container = null)
    {
        $this->callable = $closure;
        $this->container = $container;
    }

    /**
     * {@inheritdoc}
     */
    public function handleRequest(Request $request): Promise
    {
        $params = $request->getAttribute(Router::class);

        if (is_null($this->container)) {
            return call($this->closure, $request, $params);
        }

        return call($this->closure, $request, $params, $this->container);
    }
}
