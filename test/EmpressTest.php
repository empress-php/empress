<?php

declare(strict_types=1);

namespace Empress\Test;

use Amp\PHPUnit\AsyncTestCase;
use Empress\Application;
use Empress\ConfigurationBuilder;
use Empress\Empress;
use Empress\Exception\StartupException;
use Psr\Log\NullLogger;

final class EmpressTest extends AsyncTestCase
{
    public function testBootNoRoutes(): \Generator
    {
        $this->expectException(StartupException::class);

        $app = Application::create(
            1234,
            (new ConfigurationBuilder())
                ->withLogger(new NullLogger())
                ->build()
        );
        $empress = new Empress($app);

        yield $empress->boot();
    }
}
