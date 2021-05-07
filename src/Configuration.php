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
final class Configuration
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

    /**
     * @var string[]
     */
    private array $hosts;

    private int $port = 1337;

    private ?LoggerInterface $requestLogger = null;

    public function __construct()
    {
        $this->options = new Options();
        $this->sessionStorage = new InMemoryStorage();
        $this->hosts = ['0.0.0.0', '[::]'];
    }

    public static function create(): self
    {
        return new self();
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
        if ($this->staticContentPath === null) {
            return null;
        }

        return new DocumentRoot($this->staticContentPath);
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

    public function getHosts(): array
    {
        return $this->hosts;
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
    public function withMiddleware(Middleware $middleware): self
    {
        $this->middlewares[] = $middleware;

        return $this;
    }

    /**
     * Adds http-server options.
     */
    public function withServerOptions(Options $options): self
    {
        $this->options = $options;

        return $this;
    }

    /**
     * Sets path that will be used for serving static files.
     */
    public function withStaticContentPath(string $path): self
    {
        $this->staticContentPath = $path;

        return $this;
    }

    public function withSessionStorage(Storage $storage): self
    {
        $this->sessionStorage = $storage;

        return $this;
    }

    public function withTls(string $certFileName, int $port, ?string $keyFileName = null): self
    {
        $cert = new Certificate($certFileName, $keyFileName);
        $this->tlsContext = (new ServerTlsContext())->withDefaultCertificate($cert);

        $this->tlsPort = $port;

        return $this;
    }

    public function withHosts(string ...$hosts): self
    {
        $this->hosts = $hosts;

        return $this;
    }

    public function withPort(int $port): self
    {
        $this->port = $port;

        return $this;
    }

    public function withRequestLogger(LoggerInterface $logger): self
    {
        $this->requestLogger = $logger;

        return $this;
    }
}
