<?php

declare(strict_types=1);

namespace Empress;

use Amp\Http\Server\HttpServer;
use Amp\Http\Server\Session\SessionMiddleware;
use Amp\MultiReasonException;
use Amp\Promise;
use Amp\Socket\BindContext;
use Amp\Socket\Server;
use Amp\Socket\ServerTlsContext;
use Empress\Exception\ShutdownException;
use Empress\Exception\StartupException;
use Exception;
use Throwable;
use function Amp\call;
use function Amp\Http\Server\Middleware\stack;

final class Empress
{
    private HttpServer $server;

    public function __construct(private Application $application)
    {
        $this->initializeServer();
    }

    /**
     * Initializes routes and configures the environment for the application
     * and then runs it on http-server.
     */
    public function boot(): Promise
    {
        return $this->handleMultiReasonException([$this->server, 'start'], StartupException::class);
    }

    /**
     * Stops the server. As the application implements the ServerObserver interface
     * this will also call the onStop() method on the application instance.
     */
    public function shutDown(): Promise
    {
        return $this->handleMultiReasonException([$this->server, 'stop'], ShutdownException::class);
    }

    private function initializeServer(): void
    {
        $config = $this->application->getConfiguration();
        $router = $this->application->getRouter();

        // Static content serving
        if ($handler = $config->getDocumentRootHandler()) {
            $router->setFallback($handler);
        }

        $port = $this->application->getPort();
        $hosts = $this->application->getHosts();

        $sockets = $this->buildSockets($hosts, $port);

        if (($context = $config->getTlsContext()) !== null) {
            $tlsPort = $config->getTlsPort();

            \assert($tlsPort !== null);

            $tlsSockets = $this->buildTlsSockets($hosts, $tlsPort, $context);
            $sockets = \array_merge($sockets, $tlsSockets);
        }

        $middlewares = $config->getMiddlewares();

        $logger = $config->getLogger();
        $sessionMiddleware = new SessionMiddleware($config->getSessionStorage());
        $options = $config->getServerOptions();

        if ($config->getDebugMode()) {
            $options = $options->withDebugMode();
        }

        $this->server = new HttpServer(
            $sockets,
            stack($router, $sessionMiddleware, ...$middlewares),
            $logger,
            $options
        );

        $this->server->attach($this->application);
    }

    /**
     * @return Server[]
     */
    private function buildSockets(array $hosts, int $port): array
    {
        $sockets = [];

        foreach ($hosts as $host) {
            $sockets[] = Server::listen($host . ':' . $port);
        }

        return $sockets;
    }

    private function buildTlsSockets(array $hosts, int $tlsPort, ServerTlsContext $context): array
    {
        $bindContext = (new BindContext())->withTlsContext($context);

        $sockets = [];

        foreach ($hosts as $host) {
            $sockets[] = Server::listen($host . ':' . $tlsPort, $bindContext);
        }

        return $sockets;
    }

    private function handleMultiReasonException(callable $callable, string $exceptionClass = Exception::class): Promise
    {
        return call(function () use ($callable, $exceptionClass) {
            try {
                yield $callable();
            } catch (MultiReasonException $e) {
                $reasons = $e->getReasons();

                if (\count($reasons) === 1) {
                    $reason = \array_shift($reasons);

                    /** @psalm-suppress InvalidThrow */
                    throw new $exceptionClass($reason->getMessage(), $reason->getCode(), $reason);
                }

                $messages = \array_map(fn (Throwable $reason) => $reason->getMessage(), $reasons);

                /** @psalm-suppress InvalidThrow */
                throw new $exceptionClass(\implode(\PHP_EOL, $messages));
            }
        });
    }
}
