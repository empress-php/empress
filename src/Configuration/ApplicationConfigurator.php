<?php

namespace Empress\Configuration;

use Amp\Http\Server\Middleware;
use Amp\Http\Server\Options;
use Amp\Http\Server\StaticContent\DocumentRoot;
use Amp\Log\ConsoleFormatter;
use Amp\Log\StreamHandler;
use Monolog\Logger;
use Psr\Log\LoggerInterface;
use function Amp\ByteStream\getStdout;

/**
 * Defines the application environment that will be used by http-server.
 * Logging, server options and middlewares are all registered using this class.
 */
class ApplicationConfigurator
{

    /** @var Middleware[] */
    private $middlewares = [];

    /** @var Options */
    private $options;

    /** @var LoggerInterface */
    private $logger;

    /** @var string|null */
    private $staticContentPath;

    public function __construct()
    {
        $this->options = new Options;
    }

    /**
     * Gets all configured middlewares.
     *
     * @return array
     */
    public function getMiddlewares(): array
    {
        return $this->middlewares;
    }

    /**
     * Adds a middleware.
     *
     * @param Middleware $middleware
     * @return self
     */
    public function addMiddleware(Middleware $middleware): self
    {
        $this->middlewares[] = $middleware;

        return $this;
    }

    /**
     * Gets the logger.
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
     * Sets the logger.
     *
     * @param LoggerInterface $logger
     * @return self
     */
    public function setLogger(LoggerInterface $logger): self
    {
        $this->logger = $logger;

        return $this;
    }

    /**
     * Gets http-server options.
     *
     * @return Options
     */
    public function getServerOptions(): Options
    {
        return $this->options;
    }

    /**
     * Sets http-server options.
     *
     * @param Options $options
     * @return self
     */
    public function setServerOptions(Options $options): self
    {
        $this->options = $options;

        return $this;
    }

    /**
     * Gets static content path.
     *
     * @return string|null
     */
    public function getStaticContentPath(): ?string
    {
        return $this->staticContentPath;
    }

    /**
     * Gets document root fallback handler.
     *
     * @return DocumentRoot|null
     */
    public function getDocumentRootHandler(): ?DocumentRoot
    {
        if ($this->getStaticContentPath()) {
            new DocumentRoot($this->getStaticContentPath());
        }

        return null;
    }

    /**
     * Sets static content path.
     *
     * @param string $path
     * @return self
     */
    public function setStaticContentPath(string $path): self
    {
        $this->staticContentPath = $path;

        return $this;
    }
}
