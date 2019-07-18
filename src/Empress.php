<?php

namespace Empress;

use Amp\Http\Server\Server;
use Amp\MultiReasonException;
use Amp\Socket;
use Empress\Exceptions\StartupException;
use Empress\Services\Providers\CoreServicesProvider;
use Empress\Services\Providers\DefaultLoggerServiceProvider;
use Empress\Util\CallableRequestHandler;
use Pimple\Container;
use Psr\Log\LoggerInterface;
use function Amp\call;
use Amp\Promise;
use Empress\Exceptions\ShutdownException;

class Empress
{
    /** @var \Pimple\Container */
    private $container;

    /** @var \Amp\Http\Server\Server */
    private $server;

    /**
     * @param mixed $container
     * @param \Psr\Log\LoggerInterface|null $logger
     * @param int $port
     */
    public function __construct(
        $container = [],
        LoggerInterface $logger = null,
        int $port = 1337
    ) {

        // Initialize DI
        if (is_array($container)) {
            $container = new Container($container);
        }

        if (!$container instanceof Container) {
            throw new \InvalidArgumentException('Expected an instance of Container');
        }

        $this->container = $container;

        if (!is_null($logger)) {
            $this->container['logger'] = $logger;
        } else {
            $loggerProvider = new DefaultLoggerServiceProvider;
            $loggerProvider->register($this->container);
        }

        $this->container['port'] = $port;

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

    public function run(): Promise
    {
        return $this->handleMultiReasonException($this->server->start(), StartupException::class);
    }

    public function shutDown(): Promise
    {
        return $this->handleMultiReasonException($this->server->stop(), ShutdownException::class);
    }

    private function registerCallableHandler(string $method, string $uri, callable $callable): void
    {
        $closure = \Closure::fromCallable($callable);
        $closure = $closure->bindTo($this->container);

        $this->container['router']->addRoute($method, $uri, new CallableRequestHandler($closure));
    }

    private function initializeServer()
    {
        $sockets = [
            Socket\listen('0.0.0.0:' . $this->container['port']),
            Socket\listen('[::]:' . $this->container['port']),
        ];

        $this->server = new Server($sockets, $this->container['router'], $this->container['logger'], $this->container['options']);
    }

    private function handleMultiReasonException(Promise $promise, string $exceptionClass = \Exception::class)
    {
        return call(function () use ($promise, $exceptionClass) {
            try {
                yield $promise;
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
