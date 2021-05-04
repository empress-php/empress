<?php

namespace Empress;

use Amp\Http\Server\Server;
use Amp\Http\Server\ServerObserver;
use Amp\Promise;
use Amp\Success;
use Empress\Logging\RequestLogger;
use Empress\Routing\Exception\ExceptionHandler;
use Empress\Routing\Exception\ExceptionMapper;
use Empress\Routing\Handler\HandlerCollection;
use Empress\Routing\Router;
use Empress\Routing\Routes;
use Empress\Routing\Status\StatusHandler;
use Empress\Routing\Status\StatusMapper;
use Empress\Validation\Registry\DefaultValidatorRegistry;
use Empress\Validation\Registry\ValidatorRegistry;
use Psr\Log\LoggerInterface;
use function Amp\call;

final class Application implements ServerObserver
{
    public function __construct(
        private Configuration $configuration,
        private ExceptionMapper $exceptionMapper,
        private StatusMapper $statusMapper,
        private Routes $routes,
        private ValidatorRegistry $validatorRegistry,
        private $onServerStart = null,
        private $onServerStop = null
    ) {
    }

    public static function create(int $port, ?LoggerInterface $requestLogger = null, Configuration $configuration = null): self
    {
        $configuration ??= new Configuration();
        $configuration->withPort($port);

        if ($requestLogger !== null) {
            $configuration->withRequestLogger($requestLogger);
        }

        return new self(
            $configuration,
            new ExceptionMapper(),
            new StatusMapper(),
            new Routes(new HandlerCollection()),
            new DefaultValidatorRegistry()
        );
    }

    public function exception(string $exceptionClass, callable $callable): self
    {
        $exceptionHandler = new ExceptionHandler($callable, $exceptionClass);

        $this->exceptionMapper->addHandler($exceptionHandler);

        return $this;
    }

    public function status(int $status, callable $callable, array $headers = []): self
    {
        $statusHandler = new StatusHandler($callable, $status, $headers);

        $this->statusMapper->addHandler($statusHandler);

        return $this;
    }

    /**
     * @param callable(Routes): void $collector
     */
    public function routes(callable $collector): self
    {
        $collector($this->routes);

        return $this;
    }

    public function getRouter(): Router
    {
        $requestLogger = $this->configuration->getRequestLogger();

        return new Router(
            $this->exceptionMapper,
            $this->statusMapper,
            $this->routes->getHandlerCollection(),
            $this->validatorRegistry,
            $requestLogger ? new RequestLogger($requestLogger) : null
        );
    }

    public function getConfiguration(): Configuration
    {
        return $this->configuration;
    }

    public function getValidatorRegistry(): ValidatorRegistry
    {
        return $this->validatorRegistry;
    }

    public function onServerStart(callable $callable): self
    {
        $this->onServerStart = $callable;

        return $this;
    }

    public function onServerStop(callable $callable): self
    {
        $this->onServerStop = $callable;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function onStart(Server $server): Promise
    {
        if (\is_callable($this->onServerStart)) {
            return call($this->onServerStart, $server);
        }

        return new Success();
    }

    /**
     * @inheritDoc
     */
    public function onStop(Server $server): Promise
    {
        if (\is_callable($this->onServerStop)) {
            return call($this->onServerStop, $server);
        }

        return new Success();
    }
}
