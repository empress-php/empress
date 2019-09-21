<?php

namespace Empress;

use Amp\Http\Server\Middleware;
use Amp\Http\Server\Options;
use Amp\Http\Server\Server;
use Amp\Http\Server\ServerObserver;
use Amp\Log\ConsoleFormatter;
use Amp\Promise;
use Amp\Success;
use Empress\Routing\RouteConfigurator;
use Empress\Routing\RoutesTrait;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Psr\Log\LoggerInterface;
use function Amp\ByteStream\getStdout;

abstract class AbstractApplication implements ServerObserver
{

    /** @var \Amp\Http\Server\Middleware[] */
    private $middlewares = [];
    
    abstract public function configureRoutes(RouteConfigurator $configurator): void;

    public function addMiddleware(Middleware $middleware): void
    {
        $this->middlewares[] = $middleware;
    }

    public function getMiddlewares(): array
    {
        return $this->middlewares;
    }

    public function getLogger(): LoggerInterface
    {
        $logHandler = new StreamHandler(getStdout()->getResource());
        $logHandler->setFormatter(new ConsoleFormatter);
        $logger = new Logger(static::class);
        $logger->pushHandler($logHandler);

        return $logger;
    }

    public function getOptions(): Options
    {
        return new Options;
    }

    /** @inheritDoc */
    public function onStart(Server $server): Promise
    {
        return new Success();
    }

    /** @inheritDoc */
    public function onStop(Server $server): Promise
    {
        return new Success();
    }
}
