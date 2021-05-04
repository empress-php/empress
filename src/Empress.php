<?php

namespace Empress;

use Amp\Http\Server\HttpServer;
use Amp\Http\Server\Session\SessionMiddleware;
use Amp\MultiReasonException;
use Amp\Promise;
use Amp\Socket\BindContext;
use Amp\Socket\Server;
use Closure;
use Empress\Exception\ShutdownException;
use Empress\Exception\StartupException;
use Empress\Logging\DefaultLogger;
use Exception;
use Throwable;
use function Amp\ByteStream\getStdout;
use function Amp\call;
use function Amp\Http\Server\Middleware\stack;

class Empress
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
        $closure = Closure::fromCallable([$this->server, 'start']);

        return $this->handleMultiReasonException($closure, StartupException::class);
    }

    /**
     * Stops the server. As the application implements the ServerObserver interface
     * this will also call the onStop() method on the application instance.
     */
    public function shutDown(): Promise
    {
        $closure = Closure::fromCallable([$this->server, 'stop']);

        return $this->handleMultiReasonException($closure, ShutdownException::class);
    }

    private function initializeServer(): void
    {
        $router = $this->application->getRouter();
        $config = $this->application->getConfiguration();

        $middlewares = $config->getMiddlewares();
        $sessionMiddleware = new SessionMiddleware($config->getSessionStorage());

        $logger = new DefaultLogger('Empress', getStdout());
        $options = $config->getServerOptions();
        $port = $config->getPort();

        // Static content serving
        if ($handler = $config->getDocumentRootHandler()) {
            $router->setFallback($handler);
        }

        $sockets = [
            Server::listen('0.0.0.0:' . $port),
            Server::listen('[::]:' . $port),
        ];

        if (!\is_null($context = $config->getTlsContext())) {
            $tlsPort = $config->getTlsPort();
            $bindContext = (new BindContext())->withTlsContext($context);

            $sockets[] = Server::listen('0.0.0.0:' . $tlsPort, $bindContext);
            $sockets[] = Server::listen('[::]:' . $tlsPort, $bindContext);
        }

        $this->server = new HttpServer(
            $sockets,
            stack($router, $sessionMiddleware, ...$middlewares),
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

                    /** @psalm-suppress InvalidThrow */
                    throw new $exceptionClass($reason->getMessage(), $reason->getCode(), $reason);
                }

                $messages = \array_map(function (Throwable $reason) {
                    return $reason->getMessage();
                }, $reasons);

                /** @psalm-suppress InvalidThrow */
                throw new $exceptionClass(\implode(PHP_EOL, $messages));
            }
        });
    }
}
