<?php

namespace Empress;

use Amp\Http\Server\Router;
use Amp\Http\Server\Server;
use Amp\MultiReasonException;
use Amp\Promise;
use Amp\Socket;
use Closure;
use Empress\Configuration\ApplicationConfigurator;
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

    /** @var ApplicationConfigurator */
    private $applicationConfigurator;

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
        $this->applicationConfigurator = new ApplicationConfigurator;
    }

    /**
     * Initializes routes and configures the environment for the application
     * and then runs it on http-server.
     *
     * @return Promise
     */
    public function boot(): Promise
    {
        $this->initializeApplication();
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
        $this->router = $routerBuilder->getRouter();
        $this->applicationConfigurator = $this->application->configureApplication();
    }

    private function initializeServer(): void
    {
        $middlewares = $this->applicationConfigurator->getMiddlewares();
        $logger = $this->applicationConfigurator->getLogger();
        $options = $this->applicationConfigurator->getServerOptions();

        // Static content serving
        if ($handler = $this->applicationConfigurator->getDocumentRootHandler()) {
            $this->router->setFallback($handler);
        }

        // TODO: TLS
        // TODO: Session

        $sockets = [
            Socket\listen('0.0.0.0:' . $this->port),
            Socket\listen('[::]:' . $this->port),
        ];

        $this->server = new Server(
            $sockets,
            stack(
                $this->router,
                ...$middlewares
            ),
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
