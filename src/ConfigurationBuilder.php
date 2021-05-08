<?php

namespace Empress;

use Amp\Http\Server\Middleware;
use Amp\Http\Server\Options;
use Amp\Http\Server\Session\InMemoryStorage;
use Amp\Http\Server\Session\Storage;
use Amp\Socket\Certificate;
use Amp\Socket\ServerTlsContext;
use Psr\Log\LoggerInterface;

final class ConfigurationBuilder
{

    /**
     * @var Middleware[]
     */

    private array $middlewares = [];

    private Options $options;

    private ?ServerTlsContext $tlsContext = null;

    private ?int $tlsPort = null;

    private ?string $staticContentPath = null;

    private Storage $sessionStorage;

    private ?LoggerInterface $requestLogger = null;

    public function __construct()
    {
        $this->options = new Options();
        $this->sessionStorage = new InMemoryStorage();
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

    public function withRequestLogger(LoggerInterface $logger): self
    {
        $this->requestLogger = $logger;

        return $this;
    }

    public function build(): Configuration
    {
        return new Configuration(
            $this->middlewares,
            $this->options,
            $this->sessionStorage,
            $this->staticContentPath,
            $this->tlsContext,
            $this->tlsPort,
            $this->requestLogger
        );
    }
}
