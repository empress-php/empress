<?php

namespace Empress;

use Amp\Http\Server\Server;
use Amp\Http\Server\ServerObserver;
use Amp\Promise;
use Amp\Success;
use Empress\Routing\Exception\ExceptionHandler;
use Empress\Routing\Exception\ExceptionMapper;
use Empress\Routing\Handler\HandlerCollection;
use Empress\Routing\RouteCollector\RouteCollectorInterface;
use Empress\Routing\Router;
use Empress\Routing\Routes;
use Empress\Routing\Status\StatusHandler;
use Empress\Routing\Status\StatusMapper;

/**
 * Defines an application object that will be run against http-server.
 * Since it implements the ServerObserver interface it has two
 * lifecycle methods - onStart() and onStop() that can be used
 * when the application is booted and shut down respectively.
 */
class Application implements ServerObserver
{
    protected Configuration $config;

    private ExceptionMapper $exceptionMapper;

    private StatusMapper $statusMapper;

    private Routes $routes;

    public function __construct(Configuration $config = null)
    {
        $this->config = $config ?? new Configuration();

        $this->exceptionMapper = new ExceptionMapper();
        $this->statusMapper = new StatusMapper();
        $this->routes = new Routes(new HandlerCollection());
    }

    public static function create(int $port, Configuration $configuration = null): self
    {
        $configuration ??= new Configuration();
        $configuration->withPort($port);

        return new self($configuration);
    }

    public function exception(string $exceptionClass, callable $callable): self
    {
        $exceptionHandler = new ExceptionHandler($callable, $exceptionClass);

        $this->exceptionMapper->addHandler($exceptionHandler);

        return $this;
    }

    public function status(int $status, callable $callable, array $headers = []): self
    {
        $statusHandler = new StatusHandler($callable, $status, $headers);

        $this->statusMapper->addHandler($statusHandler);

        return $this;
    }

    public function routes(RouteCollectorInterface|callable $collector): void
    {
        $collector($this->routes);
    }

    public function getRouter(): Router
    {
        return new Router(
            $this->exceptionMapper,
            $this->statusMapper,
            $this->routes->getHandlerCollection()
        );
    }

    public function getConfiguration(): Configuration
    {
        return $this->config;
    }

    /**
     * @inheritDoc
     */
    public function onStart(Server $server): Promise
    {
        return new Success();
    }

    /**
     * @inheritDoc
     */
    public function onStop(Server $server): Promise
    {
        return new Success();
    }
}
