<?php

namespace Empress\Test\Configuration;

use Amp\Http\Server\Middleware;
use Amp\Http\Server\Options;
use Amp\Http\Server\Session\Storage;
use Empress\Configuration\ApplicationConfiguration;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class ApplicationConfigurationTest extends TestCase
{
    /**
     * @var ApplicationConfiguration
     */
    private $configuration;

    public function setUp(): void
    {
        $this->configuration = new ApplicationConfiguration();
    }

    public function testWithTls()
    {
        $this->assertNull($this->configuration->getTlsContext());
        $this->assertNull($this->configuration->getTlsPort());

        $this->configuration->withTls('some.cert', 1024);

        $this->assertNotNull($this->configuration->getTlsContext());
        $this->assertEquals(1024, $this->configuration->getTlsPort());
    }

    public function testWithStaticContentPath()
    {
        $this->assertNull($this->configuration->getStaticContentPath());
        $this->assertNull($this->configuration->getDocumentRootHandler());

        $this->configuration->withStaticContentPath('/');

        $this->assertEquals('/', $this->configuration->getStaticContentPath());
        $this->assertNotNull($this->configuration->getDocumentRootHandler());
    }

    public function testWithMiddleware()
    {
        $this->assertEmpty($this->configuration->getMiddlewares());

        $middleware = $this->createMock(Middleware::class);
        $this->configuration->withMiddleware($middleware);
        $middlewares = $this->configuration->getMiddlewares();

        $this->assertContains($middleware, $middlewares);
    }

    public function testWithLogger()
    {
        $this->assertNotNull($this->configuration->getLogger());

        $logger = $this->createMock(LoggerInterface::class);
        $this->configuration->withLogger($logger);

        $this->assertSame($logger, $this->configuration->getLogger());
    }

    public function testWithSessionStorage()
    {
        $this->assertNotNull($this->configuration->getSessionStorage());

        $storage = $this->createMock(Storage::class);
        $this->configuration->withSessionStorage($storage);

        $this->assertSame($storage, $this->configuration->getSessionStorage());
    }

    public function testWithServerOptions()
    {
        $this->assertNotNull($this->configuration->getServerOptions());

        $options = new Options();
        $this->configuration->withServerOptions($options);

        $this->assertSame($options, $this->configuration->getServerOptions());
    }

    public function testWithPort()
    {
        $this->assertNotNull($this->configuration->getPort());

        $this->configuration->withPort(1234);

        $this->assertEquals(1234, $this->configuration->getPort());
    }
}
