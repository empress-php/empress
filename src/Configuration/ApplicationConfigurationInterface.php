<?php

namespace Empress\Configuration;

use Amp\Http\Server\Options;
use Amp\Http\Server\Session\Storage;
use Amp\Http\Server\StaticContent\DocumentRoot;
use Amp\Socket\ServerTlsContext;
use Psr\Log\LoggerInterface;

interface ApplicationConfigurationInterface
{
    /**
     * @return array
     */
    public function getMiddlewares(): array;

    /**
     * @return LoggerInterface
     */
    public function getLogger(): LoggerInterface;

    /**
     * @return Options
     */
    public function getServerOptions(): Options;

    /**
     * @return string|null
     */
    public function getStaticContentPath(): ?string;

    /**
     * @return DocumentRoot|null
     */
    public function getDocumentRootHandler(): ?DocumentRoot;

    /**
     * @return Storage
     */
    public function getSessionStorage(): Storage;

    /**
     * @return ServerTlsContext|null
     */
    public function getTlsContext(): ?ServerTlsContext;

    /**
     * @return int|null
     */
    public function getTlsPort(): ?int;

    /**
     * @return int
     */
    public function getPort(): int;
}
