<?php

namespace Empress\Configuration;

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
 * Logging, server options and middlewares are all registered using this class.
 */
class ApplicationConfiguration implements ApplicationConfigurationInterface
{
    /** @var Middleware[] */
    private $middlewares = [];

    /** @var Options */
    private $options;

    /** @var LoggerInterface */
    private $logger;

    /** @var string|null */
    private $staticContentPath;

    /** @var Storage */
    private $sessionStorage;

    /** @var ServerTlsContext */
    private $tlsContext;

    /** @var int|null */
    private $tlsPort;

    /** @var int */
    private $port = 1337;

    public function __construct()
    {
        $this->options = new Options;
        $this->sessionStorage = new InMemoryStorage();
    }

    /**
     * @inheritDoc
     */
    public function getMiddlewares(): array
    {
        return $this->middlewares;
    }

    /**
     * @inheritDoc
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
     * @inheritDoc
     */
    public function getServerOptions(): Options
    {
        return $this->options;
    }

    /**
     * @inheritDoc
     */
    public function getStaticContentPath(): ?string
    {
        return $this->staticContentPath;
    }

    /**
     * @inheritDoc
     */
    public function getDocumentRootHandler(): ?DocumentRoot
    {
        if ($this->getStaticContentPath()) {
            return new DocumentRoot($this->getStaticContentPath());
        }

        return null;
    }

    /**
     * @inheritDoc
     */
    public function getSessionStorage(): Storage
    {
        return $this->sessionStorage;
    }

    /**
     * @inheritDoc
     */
    public function getTlsContext(): ?ServerTlsContext
    {
        return $this->tlsContext;
    }

    public function getTlsPort(): ?int
    {
        return $this->tlsPort;
    }

    /**
     * @inheritDoc
     */
    public function getPort(): int
    {
        return $this->port;
    }

    /**
     * @param Middleware $middleware
     * @return self
     */
    public function withMiddleware(Middleware $middleware): self
    {
        $this->middlewares[] = $middleware;

        return $this;
    }

    /**
     * @param LoggerInterface $logger
     * @return self
     */
    public function withLogger(LoggerInterface $logger): self
    {
        $this->logger = $logger;

        return $this;
    }

    /**
     * @param Options $options
     * @return self
     */
    public function withServerOptions(Options $options): self
    {
        $this->options = $options;

        return $this;
    }

    /**
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
     * @param string $keyFileName
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
