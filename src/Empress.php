<?php

namespace Empress;

use Amp\Http\Server\Router;
use Amp\Http\Server\Server;
use Amp\Http\Server\Session\SessionMiddleware;
use Amp\MultiReasonException;
use Amp\Promise;
use Amp\Socket;
use Closure;
use Empress\Configuration\ApplicationConfiguration;
use Empress\Exception\ShutdownException;
use Empress\Exception\StartupException;
use Empress\Routing\RouteConfigurator;
use Exception;
use Throwable;
use function Amp\call;
use function Amp\Http\Server\Middleware\stack;

class Empress
{
    /** @var Server */
    private $server;

    /** @var AbstractApplication */
    private $application;

    /** @var int */
    private $port;

    /** @var ApplicationConfiguration */
    private $applicationConfiguration;

    /** @var Router */
    private $router;

    /**
     * @var RouteConfigurator
     */
    private $routeConfigurator;

    /**
     * Empress constructor.
     * @param AbstractApplication $application
     */
    public function __construct(AbstractApplication $application)
    {
        $this->application = $application;
        $this->applicationConfiguration = new ApplicationConfiguration();
        $this->routeConfigurator = new RouteConfigurator();
    }

    /**
     * Initializes routes and configures the environment for the application
     * and then runs it on http-server.
     *
     * @return Promise
     * @throws Socket\SocketException
     */
    public function boot(): Promise
    {
        $this->initializeServer();

        $closure = Closure::fromCallable([$this->server, 'start']);
        return $this->handleMultiReasonException($closure, StartupException::class);
    }

    /**
     * Stops the server. As the application implements the ServerObserver interface
     * this will also call the onStop() method on the application instance.
     *
     * @return Promise
     */
    public function shutDown(): Promise
    {
        $closure = Closure::fromCallable([$this->server, 'stop']);
        return $this->handleMultiReasonException($closure, ShutdownException::class);
    }

    /**
     * @throws Socket\SocketException
     */
    private function initializeServer(): void
    {
        $this->initializeApplication();

        $sessionMiddleware = new SessionMiddleware($this->applicationConfiguration->getSessionStorage());
        $logger = $this->applicationConfiguration->getLogger();
        $options = $this->applicationConfiguration->getServerOptions();
        $port = $this->applicationConfiguration->getPort();

        // Static content serving
        if ($handler = $this->applicationConfiguration->getDocumentRootHandler()) {
            $this->router->setFallback($handler);
        }

        $sockets = [
            Socket\listen('0.0.0.0:' . $port),
            Socket\listen('[::]:' . $port),
        ];

        if (!\is_null($context = $this->applicationConfiguration->getTlsContext())) {
            $tlsPort = $this->applicationConfiguration->getTlsPort();
            $sockets[] = Socket\listen('0.0.0.0:' . $tlsPort, null, $context);
            $sockets[] = Socket\listen('[::]:' . $tlsPort, null, $context);
        }

        $this->server = new Server(
            $sockets,
            stack($this->router, $sessionMiddleware),
            $logger,
            $options
        );

        $this->server->attach($this->application);
    }

    private function initializeApplication()
    {
        $this->application->configureApplication($this->applicationConfiguration);
        $this->application->configureRoutes($this->routeConfigurator);

        $this->router = $this->routeConfigurator->getRouter();
        $this->router->stack(...$this->applicationConfiguration->getMiddlewares());
    }

    /**
     * @param Closure $closure
     * @param string $exceptionClass
     * @return Promise
     */
    private function handleMultiReasonException(Closure $closure, string $exceptionClass = Exception::class): Promise
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

                $messages = \array_map(function (Throwable $reason) {
                    return $reason->getMessage();
                }, $reasons);

                throw new $exceptionClass(\implode(PHP_EOL, $messages));
            }
        });
    }
}
