<?php

namespace Empress;

use Amp\Http\Server\Server;
use Amp\MultiReasonException;
use Amp\Promise;
use Amp\Socket;
use Empress\Exception\ShutdownException;
use Empress\Exception\StartupException;
use Empress\Provider\CoreServicesProvider;
use Empress\Provider\DefaultLoggerProvider;
use Empress\Internal\RequestHandler;
use Pimple\Container;
use Pimple\ServiceProviderInterface;
use Psr\Log\LoggerInterface;
use function Amp\call;

class Empress
{
    /** @var \Pimple\Container */
    private $container;

    /** @var \Amp\Http\Server\Server */
    private $server;

    /**
     * @param \Psr\Log\LoggerInterface $logger
     * @param int $port
     */
    public function __construct(int $port = 1337, LoggerInterface $logger = null)
    {
        $this->port = $port;

        $this->container = new Container;

        if (!is_null($logger)) {
            $this->container['logger'] = $logger;
        } else {
            $loggerProvider = new DefaultLoggerProvider;
            $this->container->register($loggerProvider);
        }

        $provider = new CoreServicesProvider;
        $provider->register($this->container);

        // Prepare server instance
        $this->initializeServer();
    }

    public function get(string $uri, callable $callable): void
    {
        $this->registerCallableHandler('GET', $uri, $callable);
    }

    public function post(string $uri, callable $callable): void
    {
        $this->registerCallableHandler('POST', $uri, $callable);
    }

    public function put(string $uri, callable $callable): void
    {
        $this->registerCallableHandler('PUT', $uri, $callable);
    }

    public function patch(string $uri, callable $callable): void
    {
        $this->registerCallableHandler('PATCH', $uri, $callable);
    }

    public function delete(string $uri, callable $callable): void
    {
        $this->registerCallableHandler('DELETE', $uri, $callable);
    }

    public function head(string $uri, callable $callable): void
    {
        $this->registerCallableHandler('HEAD', $uri, $callable);
    }

    public function run(): Promise
    {
        $closure = \Closure::fromCallable([$this->server, 'start']);
        return $this->handleMultiReasonException($closure, StartupException::class);
    }

    public function shutDown(): Promise
    {
        $closure = \Closure::fromCallable([$this->server, 'stop']);
        return $this->handleMultiReasonException($closure, ShutdownException::class);
    }

    public function register(string $providerClass)
    {
        $provider = new $providerClass;

        if (!$provider instanceof ServiceProviderInterface) {
            throw new \InvalidArgumentException(sprintf('Provider must implement %s', ServiceProviderInterface::class));
        }

        $provider->register($this->container);
    }

    private function registerCallableHandler(string $method, string $uri, callable $callable): void
    {
        $closure = \Closure::fromCallable($callable);
        $closure = $closure->bindTo($this->container);

        $this->container['router']->addRoute($method, $uri, new RequestHandler($closure));
    }

    private function initializeServer(): void
    {
        $sockets = [
            Socket\listen('0.0.0.0:' . $this->port),
            Socket\listen('[::]:' . $this->port),
        ];

        $this->server = new Server($sockets, $this->container['router'], $this->container['logger'], $this->container['options']);
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
}
