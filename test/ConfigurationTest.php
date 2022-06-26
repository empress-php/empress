<?php

declare(strict_types=1);

namespace Empress\Test;

use Amp\Http\Server\Middleware;
use Amp\Http\Server\Options;
use Amp\Http\Server\Session\Storage;
use Empress\ConfigurationBuilder;
use PHPUnit\Framework\TestCase;

final class ConfigurationTest extends TestCase
{
    private ConfigurationBuilder $builder;

    public function setUp(): void
    {
        $this->builder = new ConfigurationBuilder();
    }

    public function testWithTls(): void
    {
        $configuration = $this->builder
            ->withTls('some.cert', 1024)
            ->build();


        self::assertNotNull($configuration->getTlsContext());
        self::assertSame(1024, $configuration->getTlsPort());
    }

    public function testWithStaticContentPath(): void
    {
        $configuration = $this->builder
            ->withStaticContentPath('/')
            ->build();

        self::assertSame('/', $configuration->getStaticContentPath());
        self::assertNotNull($configuration->getDocumentRootHandler());
    }

    public function testWithMiddleware(): void
    {
        $middleware = $this->createMock(Middleware::class);
        $configuration = $this->builder
            ->withMiddleware($middleware)
            ->build();

        $middlewares = $configuration->getMiddlewares();

        self::assertContains($middleware, $middlewares);
    }

    public function testWithSessionStorage(): void
    {
        $storage = $this->createMock(Storage::class);
        $configuration = $this->builder->withSessionStorage($storage)->build();

        self::assertSame($storage, $configuration->getSessionStorage());
    }

    public function testWithServerOptions(): void
    {
        $options = new Options();
        $configuration = $this->builder
            ->withServerOptions($options)
            ->build();

        self::assertSame($options, $configuration->getServerOptions());
    }
}
