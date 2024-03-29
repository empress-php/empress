<?php

declare(strict_types=1);

namespace Empress;

use Amp\Http\Server\Server;
use Amp\Http\Server\ServerObserver;
use Amp\Promise;
use Amp\Success;
use Empress\Routing\Handler\HandlerCollection;
use Empress\Routing\Mapping\ContentTypeMatcher;
use Empress\Routing\Mapping\Exception\ExceptionHandler;
use Empress\Routing\Mapping\Exception\ExceptionMapper;
use Empress\Routing\Mapping\Status\StatusHandler;
use Empress\Routing\Mapping\Status\StatusMapper;
use Empress\Routing\Router;
use Empress\Routing\Routes;
use Empress\Validation\Registry\DefaultValidatorRegistry;
use Empress\Validation\Registry\ValidatorRegistryInterface;
use function Amp\call;

final class Application implements ServerObserver
{
    public function __construct(
        private int $port,
        private array $hosts,
        private Configuration $configuration,
        private ExceptionMapper $exceptionMapper,
        private StatusMapper $statusMapper,
        private Routes $routes,
        private ValidatorRegistryInterface $validatorRegistry,
        private $onServerStart = null,
        private $onServerStop = null
    ) {
    }

    public static function create(int $port, ?Configuration $configuration = null, array $hosts = ['0.0.0.0', '[::]']): self
    {
        $contentTypeMatcher = new ContentTypeMatcher();

        return new self(
            $port,
            $hosts,
            $configuration ?? (new ConfigurationBuilder())->build(),
            new ExceptionMapper($contentTypeMatcher),
            new StatusMapper($contentTypeMatcher),
            new Routes(new HandlerCollection()),
            new DefaultValidatorRegistry()
        );
    }

    public function exception(string $exceptionClass, callable $callable, ?string $contentType = null): self
    {
        $exceptionHandler = new ExceptionHandler($callable, $exceptionClass, $contentType);

        $this->exceptionMapper->addHandler($exceptionHandler);

        return $this;
    }

    public function status(int $status, callable $callable, ?string $contentType = null): self
    {
        $statusHandler = new StatusHandler($callable, $status, $contentType);

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
        return new Router(
            $this->exceptionMapper,
            $this->statusMapper,
            $this->routes->getHandlerCollection(),
            $this->validatorRegistry
        );
    }

    public function getConfiguration(): Configuration
    {
        return $this->configuration;
    }

    public function getValidatorRegistry(): ValidatorRegistryInterface
    {
        return $this->validatorRegistry;
    }

    public function getPort(): int
    {
        return $this->port;
    }

    public function getHosts(): array
    {
        return $this->hosts;
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
