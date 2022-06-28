<?php

declare(strict_types=1);

namespace Empress;

use Amp\Http\Server\Middleware;
use Amp\Http\Server\Options;
use Amp\Http\Server\Session\InMemoryStorage;
use Amp\Http\Server\Session\Storage;
use Amp\Socket\Certificate;
use Amp\Socket\ServerTlsContext;
use Empress\Logging\DefaultLogger;
use Psr\Log\LoggerInterface;
use function Amp\ByteStream\getStdout;

final class ConfigurationBuilder
{
    /**
     * @var Middleware[]
     */
    private array $middlewares = [];

    private Options $options;

    private LoggerInterface $logger;

    private bool $debugMode = false;

    private ?ServerTlsContext $tlsContext = null;

    private ?int $tlsPort = null;

    private ?string $staticContentPath = null;

    private Storage $sessionStorage;


    public function __construct()
    {
        $this->options = new Options();
        $this->sessionStorage = new InMemoryStorage();
        $this->logger = new DefaultLogger('Empress', getStdout());
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

    public function withLogger(LoggerInterface $logger): self
    {
        $this->logger = $logger;

        return $this;
    }

    public function withDebugMode(): self
    {
        $this->debugMode = true;

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

    public function build(): Configuration
    {
        return new Configuration(
            $this->middlewares,
            $this->options,
            $this->sessionStorage,
            $this->logger,
            $this->debugMode,
            $this->staticContentPath,
            $this->tlsContext,
            $this->tlsPort,
        );
    }
}
