<?php

namespace Empress;

use Amp\Http\Server\Server;
use Amp\Http\Server\Session\SessionMiddleware;
use Amp\Log\ConsoleFormatter;
use Amp\Log\StreamHandler;
use Amp\MultiReasonException;
use Amp\Promise;
use Amp\Socket;
use Closure;
use Empress\Exception\ShutdownException;
use Empress\Exception\StartupException;
use Exception;
use Monolog\Logger;
use Psr\Log\LoggerInterface;
use Throwable;
use function Amp\ByteStream\getStdout;
use function Amp\call;
use function Amp\Http\Server\Middleware\stack;

class Empress
{
    private Server $server;

    private Application $application;

    public function __construct(Application $application)
    {
        $this->application = $application;
    }

    /**
     * Initializes routes and configures the environment for the application
     * and then runs it on http-server.

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

        $sessionMiddleware = new SessionMiddleware($config->getSessionStorage());
        $logger = $this->getLogger();
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

    private function getLogger(): LoggerInterface
    {
        $logHandler = new StreamHandler(getStdout());
        $logHandler->setFormatter(new ConsoleFormatter);
        $logger = new Logger('Empress');
        $logger->pushHandler($logHandler);

        return $logger;
    }
}
