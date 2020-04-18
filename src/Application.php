<?php

namespace Empress;

use Amp\Http\Server\Server;
use Amp\Http\Server\ServerObserver;
use Amp\Promise;
use Amp\Success;
use Empress\Routing\Exception\ExceptionHandler;
use Empress\Routing\RootGroup;
use Empress\Routing\Router;
use Empress\Routing\Routes;
use Empress\Routing\Status\StatusHandler;

/**
 * Defines an application object that will be run against http-server.
 * Since it implements the ServerObserver interface it has two
 * lifecycle methods - onStart() and onStop() that can be used
 * when the application is booted and shut down respectively.
 */
class Application implements ServerObserver
{

    /**
     * @var Routes
     */
    protected $routes;

    /**
     * @var Configuration
     */
    protected $config;

    /**
     * @var Router
     */
    private $router;

    /**
     * @var RootGroup
     */
    private $rootGroup;

    public function __construct()
    {
        $this->rootGroup = new RootGroup();
        $this->routes = new Routes($this->rootGroup);

        $this->config = new Configuration();
        $this->router = new Router();
    }

    /**
     * @return Routes
     */
    public function routes(): Routes
    {
        return $this->routes;
    }

    /**
     * @return Configuration
     */
    public function config(): Configuration
    {
        return $this->config;
    }

    public function exception(string $exceptionClass, callable $callable): self
    {
        $exceptionHandler = new ExceptionHandler($exceptionClass, $callable);

        $this->router->addExceptionHandler($exceptionHandler);

        return $this;
    }

    public function status(int $status, callable $callable, array $headers = []): self
    {
        $statusHandler = new StatusHandler($status, $callable, $headers);

        $this->router->addStatusHandler($statusHandler);

        return $this;
    }

    public function getRouter(): Router
    {
        $this->router->addEntries($this->rootGroup);

        return $this->router;
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
