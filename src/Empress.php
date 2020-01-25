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
use Empress\Routing\RouterBuilder;
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
     * @param AbstractApplication $application
     * @param int $port
     */
    public function __construct(AbstractApplication $application, int $port = 1337)
    {
        $this->application = $application;
        $this->port = $port;
        $this->applicationConfiguration = new ApplicationConfiguration;
    }

    /**
     * Initializes routes and configures the environment for the application
     * and then runs it on http-server.
     *
     * @return Promise
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

    private function initializeApplication()
    {
        $routeConfigurator = $this->application->configureRoutes();
        $routerBuilder = new RouterBuilder($routeConfigurator);

        $this->applicationConfiguration = $this->application->getApplicationConfiguration();

        $this->router = $routerBuilder->getRouter();
        $this->router->stack(...$this->applicationConfiguration->getMiddlewares());
    }

    private function initializeServer(): void
    {
        $this->initializeApplication();

        $sessionMiddleware = new SessionMiddleware($this->applicationConfiguration->getSessionStorage());
        $logger = $this->applicationConfiguration->getLogger();
        $options = $this->applicationConfiguration->getServerOptions();

        // Static content serving
        if ($handler = $this->applicationConfiguration->getDocumentRootHandler()) {
            $this->router->setFallback($handler);
        }

        $sockets = [
            Socket\listen('0.0.0.0:' . $this->port),
            Socket\listen('[::]:' . $this->port),
        ];

        if (!\is_null($context = $this->applicationConfiguration->getTlsContext())) {
            $port = $this->applicationConfiguration->getTlsPort();
            $sockets[] = Socket\listen('0.0.0.0:' . $port, null, $context);
            $sockets[] = Socket\listen('[::]:' . $port, null, $context);
        }

        $this->server = new Server(
            $sockets,
            stack($this->router, $sessionMiddleware),
            $logger,
            $options
        );

        $this->server->attach($this->application);
    }

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
