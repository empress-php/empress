<?php

namespace Empress;

use Amp\Http\Server\Driver\Http2Driver;
use Amp\Http\Server\Server;
use Amp\Loop\DriverFactory;
use Amp\MultiReasonException;
use Amp\Promise;
use Amp\Socket;
use Amp\Socket\ServerTlsContext;
use Empress\Exception\ShutdownException;
use Empress\Exception\StartupException;
use Empress\Routing\RouteConfigurator;
use Empress\Routing\RouterBuilder;
use function Amp\call;
use function Amp\Http\Server\Middleware\stack;

class Empress
{
    /** @var \Amp\Http\Server\Server */
    private $server;

    /** @var \Empress\AbstractApplication */
    private $application;

    /**
     * @param \Empress\AbstractApplication $application
     * @param int $port
     */
    public function __construct(AbstractApplication $application, int $port = 1337)
    {
        $this->application = $application;
        $this->port = $port;
    }

    public function boot(): Promise
    {
        $this->initializeServer();

        $closure = \Closure::fromCallable([$this->server, 'start']);
        return $this->handleMultiReasonException($closure, StartupException::class);
    }

    public function shutDown(): Promise
    {
        $closure = \Closure::fromCallable([$this->server, 'stop']);
        return $this->handleMultiReasonException($closure, ShutdownException::class);
    }

    private function initializeServer(): void
    {
        $routeConfigurator = new RouteConfigurator;
        $this->application->configureRoutes($routeConfigurator);

        $routerBuilder = new RouterBuilder($routeConfigurator);
        $router = $routerBuilder->getRouter();

        $applicationConfigurator = new ApplicationConfigurator;
        $this->application->configureApplication($applicationConfigurator);

        $middlewares = $applicationConfigurator->getMiddlewares();
        $logger = $applicationConfigurator->getLogger();
        $options = $applicationConfigurator->getServerOptions();

        $sockets = [
            Socket\listen('0.0.0.0:' . $this->port),
            Socket\listen('[::]:' . $this->port),
        ];

        $this->server = new Server(
            $sockets,
            stack(
                $router,
                ...$middlewares
            ),
            $logger,
            $options
        );

        $this->server->attach($this->application);
    }

    private function handleMultiReasonException(\Closure $closure, string $exceptionClass = \Exception::class): Promise
    {
        return call(function () use ($closure, $exceptionClass) {
            try {
                yield $closure();
            } catch (MultiReasonException $e) {
                $reasons = $e->getReasons();

                if (\count($reasons) === 1) {
                    $reason = \array_shift($reasons);
                    throw new $exceptionClass($reason->getMessage(), $reason->getCode(), $reason);
                }

                $messages = \array_map(function (\Throwable $reason) {
                    return $reason->getMessage();
                }, $reasons);

                throw new $exceptionClass(\implode(PHP_EOL, $messages));
            }
        });
    }
}
