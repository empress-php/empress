<?php

namespace Empress\Configuration;

use Amp\Http\Server\Options;
use Amp\Http\Server\Session\Storage;
use Amp\Http\Server\StaticContent\DocumentRoot;
use Amp\Socket\ServerTlsContext;
use Psr\Log\LoggerInterface;

class ImmutableApplicationConfiguration implements ApplicationConfigurationInterface
{
    /**
     * @var ApplicationConfiguration
     */
    private $configuration;

    public function __construct(ApplicationConfiguration $configuration)
    {
        return $this->configuration = $configuration;
    }

    /**
     * @inheritDoc
     */
    public function getMiddlewares(): array
    {
        return $this->configuration->getMiddlewares();
    }

    /**
     * @inheritDoc
     */
    public function getLogger(): LoggerInterface
    {
        return $this->configuration->getLogger();
    }

    /**
     * @inheritDoc
     */
    public function getServerOptions(): Options
    {
        return $this->configuration->getServerOptions();
    }

    /**
     * @inheritDoc
     */
    public function getStaticContentPath(): ?string
    {
        return $this->configuration->getStaticContentPath();
    }

    /**
     * @inheritDoc
     */
    public function getDocumentRootHandler(): ?DocumentRoot
    {
        return $this->configuration->getDocumentRootHandler();
    }

    /**
     * @inheritDoc
     */
    public function getSessionStorage(): Storage
    {
        return $this->configuration->getSessionStorage();
    }

    /**
     * @inheritDoc
     */
    public function getTlsContext(): ?ServerTlsContext
    {
        return $this->configuration->getTlsContext();
    }

    /**
     * @return int|null
     */
    public function getTlsPort(): ?int
    {
        return $this->configuration->getTlsPort();
    }
}
