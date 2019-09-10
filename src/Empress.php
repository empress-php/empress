<?php

namespace Empress;

use Amp\ByteStream\ResourceOutputStream;
use Amp\Http\Server\Middleware;
use Amp\Http\Server\Options;
use Amp\Http\Server\Router;
use Amp\Http\Server\Server;
use Amp\Log\ConsoleFormatter;
use Amp\MultiReasonException;
use Amp\Promise;
use Amp\Socket;
use Empress\Exception\ShutdownException;
use Empress\Exception\StartupException;
use Empress\Internal\RequestHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use Psr\Log\LoggerInterface;
use function Amp\call;
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

    /** @var \Amp\Http\Server\Router */
    private $router;

    /**
     * @param \Psr\Log\LoggerInterface $logger
     * @param int $port
     */
    public function __construct(
        ContainerInterface $container = null,
        array $middlewares = [],
        int $port = 1337,
        Options $options = null
    ) {
        $this->container = $container;
        $this->port = $port;
        $this->options = $options ?? new Options;
        $this->router = new Router;

        $this->registerMiddlewares($middlewares);
    }

    public function get(string $uri, $handler): void
    {
        $this->registerCallableHandler('GET', $uri, $handler);
    }

    public function post(string $uri, $handler): void
    {
        $this->registerCallableHandler('POST', $uri, $handler);
    }

    public function put(string $uri, $handler): void
    {
        $this->registerCallableHandler('PUT', $uri, $handler);
    }

    public function patch(string $uri, $handler): void
    {
        $this->registerCallableHandler('PATCH', $uri, $handler);
    }

    public function delete(string $uri, $handler): void
    {
        $this->registerCallableHandler('DELETE', $uri, $handler);
    }

    public function head(string $uri, $handler): void
    {
        $this->registerCallableHandler('HEAD', $uri, $handler);
    }

    public function run(): Promise
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

    private function registerMiddlewares($middlewares)
    {
        foreach ($middlewares as $middleware) {
            if (!$middleware instanceof Middleware) {
                throw new \InvalidArgumentException(sprintf('Array of objects implementing %s was expected', Middleware::class));
            }

            $this->middlewares[] = $middleware;
        }
    }

    private function registerCallableHandler(string $method, string $uri, $handler): void
    {
        $allowedMethods = $this->options->getAllowedMethods();

        if (!in_array($method, $allowedMethods, true)) {
            throw new \InvalidArgumentException(sprintf('Method %s is not allowed', $method));
        }

        if (is_array($handler)) {
            [$class, $method] = $handler;
            $service = $this->container->get($class);
            $handler = [$service, $method];
            $closure = \Closure::fromCallable($handler);

            $this->router->addRoute($method, $uri, new RequestHandler($closure));

            return;
        }
    }

    private function initializeServer(): void
    {
        try {
            $logger = $this->container->get(LoggerInterface::class);
        } catch (NotFoundExceptionInterface $e) {
            $logger = $this->getDefaultLogger();
        }

        $sockets = [
            Socket\listen('0.0.0.0:' . $this->port),
            Socket\listen('[::]:' . $this->port),
        ];

        $this->server = new Server(
            $sockets,
            stack(
                $this->router,
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

    private function getDefaultLogger()
    {
        $logHandler = new StreamHandler(new ResourceOutputStream(\STDOUT));
        $logHandler->setFormatter(new ConsoleFormatter);
        $logger = new Logger('Empress');
        $logger->pushHandler($logHandler);

        return $logger;
    }
}
