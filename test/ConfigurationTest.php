<?php

namespace Empress\Test;

use Amp\Http\Server\Middleware;
use Amp\Http\Server\Options;
use Amp\Http\Server\Session\Storage;
use Empress\Configuration;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class ConfigurationTest extends TestCase
{
    private Configuration $configuration;

    public function setUp(): void
    {
        $this->configuration = new Configuration();
    }

    public function testWithTls(): void
    {
        static::assertNull($this->configuration->getTlsContext());
        static::assertNull($this->configuration->getTlsPort());

        $this->configuration->withTls('some.cert', 1024);

        static::assertNotNull($this->configuration->getTlsContext());
        static::assertEquals(1024, $this->configuration->getTlsPort());
    }

    public function testWithStaticContentPath(): void
    {
        static::assertNull($this->configuration->getStaticContentPath());
        static::assertNull($this->configuration->getDocumentRootHandler());

        $this->configuration->withStaticContentPath('/');

        static::assertEquals('/', $this->configuration->getStaticContentPath());
        static::assertNotNull($this->configuration->getDocumentRootHandler());
    }

    public function testWithMiddleware(): void
    {
        static::assertEmpty($this->configuration->getMiddlewares());

        $middleware = $this->createMock(Middleware::class);
        $this->configuration->withMiddleware($middleware);
        $middlewares = $this->configuration->getMiddlewares();

        static::assertContains($middleware, $middlewares);
    }

    public function testWithLogger(): void
    {
        static::assertNotNull($this->configuration->getLogger());

        $logger = $this->createMock(LoggerInterface::class);
        $this->configuration->withLogger($logger);

        static::assertSame($logger, $this->configuration->getLogger());
    }

    public function testWithSessionStorage(): void
    {
        static::assertNotNull($this->configuration->getSessionStorage());

        $storage = $this->createMock(Storage::class);
        $this->configuration->withSessionStorage($storage);

        static::assertSame($storage, $this->configuration->getSessionStorage());
    }

    public function testWithServerOptions(): void
    {
        static::assertNotNull($this->configuration->getServerOptions());

        $options = new Options();
        $this->configuration->withServerOptions($options);

        static::assertSame($options, $this->configuration->getServerOptions());
    }

    public function testWithPort(): void
    {
        static::assertNotNull($this->configuration->getPort());

        $this->configuration->withPort(1234);

        static::assertEquals(1234, $this->configuration->getPort());
    }
}
