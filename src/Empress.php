<?php
namespace Empress;

use Amp\Http\Server\Server;
use Amp\Http\Server\Response;
use Amp\Http\Server\Router;
use Amp\Http\Server\Options;
use Amp\Promise;
use Amp\Socket;
use Psr\Log\LoggerInterface;
use Pimple\Container;
use Amp\Util\CallableRequestHandlerWithParams;

class Empress
{
    /** @var \Pimple\Container */
    private $container;

    /**
     * @param array $container
     * @param \Amp\Http\Server\Options $optons
     * @param int $port
     */
    public function __construct($container = [], Options $options = null, int $port = 1337)
    {
        if (is_array($container)) {
            $container = new Container($container);
        }

        if (!$container instanceof Container) {
            throw new \InvalidArgumentException('Expected an instance of Container');
        }

        $this->container = $container;
        $this->options = $options;
        $this->port = $port;

        $this->router = new Router;

        $defaultServicesProvider = new Services\DefaultServicesProvider($this);
        $defaultServicesProvider->register($this->container);
    }

    public function get(string $uri, callable $callable)
    {
        $this->registerCallableHandler('GET', $uri, $callable);
    }

    public function post(string $uri, callabe $callable)
    {
        $this->registerCallableHandler('POST', $uri, $callable);
    }

    public function run()
    {
        $sockets = [
            Socket\listen('0.0.0.0:' . $this->port),
            Socket\listen('[::]:' . $this->port),
        ];

        $server = new Server($sockets, $this->router, $this->container['logger'], $this->options);

        return $server->start();
    }

    public function getContainer(): ContainerInterface
    {
        return $this->container;
    }

    private function registerCallableHandler(string $method, string $uri, callable $callable)
    {
        if (!$callable instanceof Closure) {
            $callable = \Closure::fromCallable($callable);
        }

        $callable = $callable->bindTo($this->container);

        $this->router->addRoute($method, $uri, new CallableRequestHandlerWithParams($callable));
    }
}
