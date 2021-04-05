<?php

namespace Empress;

use Amp\Http\Server\Middleware;
use Amp\Http\Server\Options;
use Amp\Http\Server\Session\InMemoryStorage;
use Amp\Http\Server\Session\Storage;
use Amp\Http\Server\StaticContent\DocumentRoot;
use Amp\Log\ConsoleFormatter;
use Amp\Log\StreamHandler;
use Amp\Socket\Certificate;
use Amp\Socket\ServerTlsContext;
use Monolog\Logger;
use Psr\Log\LoggerInterface;
use function Amp\ByteStream\getStdout;

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

    private LoggerInterface $logger;

    private ?string $staticContentPath = null;

    private Storage $sessionStorage;

    private ?ServerTlsContext $tlsContext = null;

    private ?int $tlsPort = null;

    private int $port = 1337;

    /**
     * ApplicationConfiguration constructor.
     */
    public function __construct()
    {
        $this->options = new Options;
        $this->sessionStorage = new InMemoryStorage();
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
     * Gets logger used by http-server
     *
     * @return LoggerInterface
     */
    public function getLogger(): LoggerInterface
    {
        if (isset($this->logger)) {
            return $this->logger;
        }

        $logHandler = new StreamHandler(getStdout());
        $logHandler->setFormatter(new ConsoleFormatter);
        $this->logger = new Logger('Empress');
        $this->logger->pushHandler($logHandler);

        return $this->logger;
    }

    /**
     * Gets configured server options
     *
     * @return Options
     */
    public function getServerOptions(): Options
    {
        return $this->options;
    }

    /**
     * Gets path that will be used for serving static files.
     *
     * @return string|null
     */
    public function getStaticContentPath(): ?string
    {
        return $this->staticContentPath;
    }

    /**
     * Gets the fallback static file handler.
     *
     * @return DocumentRoot|null
     */
    public function getDocumentRootHandler(): ?DocumentRoot
    {
        if ($this->getStaticContentPath()) {
            return new DocumentRoot($this->getStaticContentPath());
        }

        return null;
    }

    /**
     * @return Storage
     */
    public function getSessionStorage(): Storage
    {
        return $this->sessionStorage;
    }

    /**
     * @return ServerTlsContext|null
     */
    public function getTlsContext(): ?ServerTlsContext
    {
        return $this->tlsContext;
    }

    /**
     * @return int|null
     */
    public function getTlsPort(): ?int
    {
        return $this->tlsPort;
    }

    /**
     * @return int
     */
    public function getPort(): int
    {
        return $this->port;
    }

    /**
     * Adds a middleware.
     *
     * @param Middleware $middleware
     * @return self
     */
    public function withMiddleware(Middleware $middleware): self
    {
        $this->middlewares[] = $middleware;

        return $this;
    }

    /**
     * Sets the logger used by http-server.
     *
     * @param LoggerInterface $logger
     * @return self
     */
    public function withLogger(LoggerInterface $logger): self
    {
        $this->logger = $logger;

        return $this;
    }

    /**
     * Adds http-server options.
     *
     * @param Options $options
     * @return self
     */
    public function withServerOptions(Options $options): self
    {
        $this->options = $options;

        return $this;
    }

    /**
     * Sets path that will be used for serving static files.
     *
     * @param string $path
     * @return self
     */
    public function withStaticContentPath(string $path): self
    {
        $this->staticContentPath = $path;

        return $this;
    }

    /**
     * @param Storage $storage
     * @return $this
     */
    public function withSessionStorage(Storage $storage): self
    {
        $this->sessionStorage = $storage;

        return $this;
    }

    /**
     * @param string $certFileName
     * @param int $port
     * @param string|null $keyFileName
     * @return $this
     */
    public function withTls(string $certFileName, int $port, ?string $keyFileName = null): self
    {
        $cert = new Certificate($certFileName, $keyFileName);
        $this->tlsContext = (new ServerTlsContext())->withDefaultCertificate($cert);

        $this->tlsPort = $port;

        return $this;
    }

    /**
     * @param int $port
     * @return $this
     */
    public function withPort(int $port): self
    {
        $this->port = $port;

        return $this;
    }
}
