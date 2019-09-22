<?php

namespace Empress;

use Amp\Http\Server\Middleware;
use Amp\Http\Server\Options;
use Amp\Log\ConsoleFormatter;
use Amp\Log\StreamHandler;
use Monolog\Logger;
use Psr\Log\LoggerInterface;
use function Amp\ByteStream\getStdout;

class ApplicationConfigurator
{

    /** @var \Amp\Http\Server\Middleware[] */
    private $middlewares;

    /** @var \Amp\Http\Server\Options */
    private $options;

    /** @var \Psr\Log\LoggerInterface */
    private $logger;

    public function __construct()
    {
    }

    public function getMiddlewares(): array
    {
        return $this->middlewares;
    }

    public function addMiddleware(Middleware $middleware): self
    {
        $this->middlewares[] = $middleware;

        return $this;
    }


    public function getLogger(): LoggerInterface
    {
        if (isset($this->logger)) {
            return $this->logger;
        }

        $logHandler = new StreamHandler(getStdout());
        $logHandler->setFormatter(new ConsoleFormatter);
        $this->logger = new Logger(static::class);
        $this->logger->pushHandler($logHandler);

        return $this->logger;
    }

    public function setLogger(LoggerInterface $logger): self
    {
        $this->logger = $logger;

        return $this;
    }

    public function getServerOptions()
    {
        $this->options ?? new Options;
    }

    public function setServerOptions(Options $options): self
    {
        $this->options = $options;

        return $this;
    }
}
