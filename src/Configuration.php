<?php

namespace Empress;

use Amp\Http\Server\Middleware;
use Amp\Http\Server\Options;
use Amp\Http\Server\Session\InMemoryStorage;
use Amp\Http\Server\Session\Storage;
use Amp\Http\Server\StaticContent\DocumentRoot;
use Amp\Socket\Certificate;
use Amp\Socket\ServerTlsContext;
use Psr\Log\LoggerInterface;

/**
 * Defines the application environment that will be used by http-server.
 * Server logging, server options and middlewares are all registered using this class.
 */
class Configuration
{
    /**
     * @var Middleware[]
     */
    private array $middlewares = [];

    private Options $options;

    private ?string $staticContentPath = null;

    private Storage $sessionStorage;

    private ?ServerTlsContext $tlsContext = null;

    private ?int $tlsPort = null;

    private int $port = 1337;

    private ?LoggerInterface $requestLogger = null;

    public function __construct()
    {
        $this->options = new Options();
        $this->sessionStorage = new InMemoryStorage();
    }

    public static function create(): static
    {
        return new static();
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
        if ($this->getStaticContentPath() === null) {
            return null;
        }

        /** @psalm-suppress PossiblyNullArgument */
        return new DocumentRoot($this->getStaticContentPath());
    }

    public function getSessionStorage(): Storage
    {
        return $this->sessionStorage;
    }

    public function getTlsContext(): ?ServerTlsContext
    {
        return $this->tlsContext;
    }

    public function getTlsPort(): ?int
    {
        return $this->tlsPort;
    }

    public function getPort(): int
    {
        return $this->port;
    }

    public function getRequestLogger(): ?LoggerInterface
    {
        return $this->requestLogger;
    }

    /**
     * Adds a middleware.
     */
    public function withMiddleware(Middleware $middleware): static
    {
        $this->middlewares[] = $middleware;

        return $this;
    }

    /**
     * Adds http-server options.
     */
    public function withServerOptions(Options $options): static
    {
        $this->options = $options;

        return $this;
    }

    /**
     * Sets path that will be used for serving static files.
     */
    public function withStaticContentPath(string $path): static
    {
        $this->staticContentPath = $path;

        return $this;
    }

    public function withSessionStorage(Storage $storage): static
    {
        $this->sessionStorage = $storage;

        return $this;
    }

    public function withTls(string $certFileName, int $port, ?string $keyFileName = null): static
    {
        $cert = new Certificate($certFileName, $keyFileName);
        $this->tlsContext = (new ServerTlsContext())->withDefaultCertificate($cert);

        $this->tlsPort = $port;

        return $this;
    }

    public function withPort(int $port): static
    {
        $this->port = $port;

        return $this;
    }

    public function withRequestLogger(LoggerInterface $logger): static
    {
        $this->requestLogger = $logger;

        return $this;
    }
}
