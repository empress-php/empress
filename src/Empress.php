<?php

namespace Empress;

use Amp\Http\Server\Middleware;
use Amp\Http\Server\Options;
use Amp\Http\Server\Server;
use Amp\Log\ConsoleFormatter;
use Amp\MultiReasonException;
use Amp\Promise;
use Amp\Socket;
use Empress\Routing\RouterBuilder;
use Empress\Exception\ShutdownException;
use Empress\Exception\StartupException;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use function Amp\call;
use function Amp\ByteStream\getStdout;
use function Amp\Http\Server\Middleware\stack;

class Empress
{
    /** @var \Psr\Container\ContainerInterface */
    private $container;

    /** @var \Amp\Http\Server\Server */
    private $server;

    /** @var \Amp\Http\Server\Middleware[] */
    private $middlewares = [];

    /** @var \Amp\Http\Server\Options */
    private $options;

    /** @var \Empress\Routing\RouterBuilder */
    private $routerBuilder;

    /**
     * @param \Psr\Log\LoggerInterface $logger
     * @param int $port
     */
    public function __construct(
        ContainerInterface $container,
        int $port = 1337
    ) {
        $this->container = $container;
        $this->routerBuilder = new RouterBuilder($container);
        $this->port = $port;
        $this->options = new Options;
    }

    public function run(): Promise
    {
        $this->initializeServer();

        $closure = \Closure::fromCallable([$this->server, 'start']);
        return $this->handleMultiReasonException($closure, StartupException::class);
    }

    public function addMiddleware(Middleware $middleware)
    {
        $this->middlewares[] = $middleware;

        return $this;
    }

    public function shutDown(): Promise
    {
        $closure = \Closure::fromCallable([$this->server, 'stop']);
        return $this->handleMultiReasonException($closure, ShutdownException::class);
    }

    public function getRouterBuilder(): RouterBuilder
    {
        return $this->routerBuilder;
    }

    private function initializeServer(): void
    {
        $logger = $this->container->has(LoggerInterface::class) ?
            $this->container->get(LoggerInterface::class) : $this->getDefaultLogger();

        $sockets = [
            Socket\listen('0.0.0.0:' . $this->port),
            Socket\listen('[::]:' . $this->port),
        ];

        $router = $this->getRouterBuilder()->getRouter();

        $this->server = new Server(
            $sockets,
            stack(
                $router,
                ...$this->middlewares
            ),
            $logger,
            $this->options
        );
    }

    private function handleMultiReasonException(\Closure $closure, string $exceptionClass = \Exception::class): Promise
    {
        return call(function () use ($closure, $exceptionClass) {
            try {
                yield $closure();
            } catch (MultiReasonException $e) {
                $reasons = $e->getReasons();

                if (count($reasons) === 1) {
                    $reason = array_shift($reasons);
                    throw new $exceptionClass($reason->getMessage(), $reason->getCode(), $reason);
                }

                $messages = array_map(function (\Throwable $reason) {
                    return $reason->getMessage();
                }, $reasons);

                throw new $exceptionClass(implode(PHP_EOL, $messages));
            }
        });
    }

    private function getDefaultLogger(): Logger
    {
        $logHandler = new StreamHandler(getStdout()->getResource());
        $logHandler->setFormatter(new ConsoleFormatter);
        $logger = new Logger('Empress');
        $logger->pushHandler($logHandler);

        return $logger;
    }
}
