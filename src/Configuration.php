<?php

declare(strict_types=1);

namespace Empress;

use Amp\Http\Server\Middleware;
use Amp\Http\Server\Options;
use Amp\Http\Server\Session\Storage;
use Amp\Http\Server\StaticContent\DocumentRoot;
use Amp\Socket\ServerTlsContext;
use Psr\Log\LoggerInterface;

/**
 * Defines the application environment that will be used by http-server.
 * Server logging, server options and middlewares are all registered using this class.
 */
final class Configuration
{
    /**
     * @param Middleware[] $middlewares
     */
    public function __construct(
        private array $middlewares,
        private Options $options,
        private Storage $sessionStorage,
        private LoggerInterface $logger,
        private bool $debugMode,
        private ?string $staticContentPath,
        private ?ServerTlsContext $tlsContext,
        private ?int $tlsPort,
    ) {
    }

    /**
     * Returns all registered middlewares.
     *
     * @return Middleware[]
     */
    public function getMiddlewares(): array
    {
        return $this->middlewares;
    }

    /**
     * Gets configured server options.
     */
    public function getServerOptions(): Options
    {
        return $this->options;
    }

    public function getSessionStorage(): Storage
    {
        return $this->sessionStorage;
    }

    public function getLogger(): LoggerInterface
    {
        return $this->logger;
    }

    public function getDebugMode(): bool
    {
        return $this->debugMode;
    }

    /**
     * Gets path that will be used for serving static files.
     */
    public function getStaticContentPath(): ?string
    {
        return $this->staticContentPath;
    }

    /**
     * Gets the fallback static file handler.
     */
    public function getDocumentRootHandler(): ?DocumentRoot
    {
        if ($this->staticContentPath === null) {
            return null;
        }

        return new DocumentRoot($this->staticContentPath);
    }

    public function getTlsContext(): ?ServerTlsContext
    {
        return $this->tlsContext;
    }

    public function getTlsPort(): ?int
    {
        return $this->tlsPort;
    }
}
