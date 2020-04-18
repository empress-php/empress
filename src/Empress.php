<?php

namespace Empress;

use Amp\Http\Server\Server;
use Amp\Http\Server\Session\SessionMiddleware;
use Amp\MultiReasonException;
use Amp\Promise;
use Amp\Socket;
use Closure;
use Empress\Configuration;
use Empress\Exception\ShutdownException;
use Empress\Exception\StartupException;
use Empress\Routing\Router;
use Empress\Routing\Routes;
use Exception;
use Throwable;
use function Amp\call;
use function Amp\Http\Server\Middleware\stack;

class Empress
{

    /**
     * @var Server
     */
    private $server;

    /**
     * @var Application
     */
    private $application;

    /**
     * @var int
     */
    private $port;

    /**
     * @var bool
     */
    private $booted;

    /**
     * Empress constructor.
     * @param Application $application
     */
    public function __construct(Application $application)
    {
        $this->application = $application;
        $this->booted = false;
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
        $router = $this->application->getRouter();
        $config = $this->application->config();

        $sessionMiddleware = new SessionMiddleware($config->getSessionStorage());
        $logger = $config->getLogger();
        $options = $config->getServerOptions();
        $port = $config->getPort();

        // Static content serving
        if ($handler = $config->getDocumentRootHandler()) {
            $router->setFallback($handler);
        }

        $sockets = [
            Socket\listen('0.0.0.0:' . $port),
            Socket\listen('[::]:' . $port),
        ];

        if (!\is_null($context = $config->getTlsContext())) {
            $tlsPort = $config->getTlsPort();
            $sockets[] = Socket\listen('0.0.0.0:' . $tlsPort, null, $context);
            $sockets[] = Socket\listen('[::]:' . $tlsPort, null, $context);
        }

        $this->server = new Server(
            $sockets,
            stack($router, $sessionMiddleware),
            $logger,
            $options
        );

        $this->server->attach($this->application);
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
