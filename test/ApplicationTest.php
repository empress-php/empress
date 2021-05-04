<?php

namespace Empress\Test;

use Amp\PHPUnit\AsyncTestCase;
use Empress\Application;
use Empress\Test\Helper\StubServerTrait;
use Psr\Log\LoggerInterface;

class ApplicationTest extends AsyncTestCase
{
    use StubServerTrait;

    public function testCreate()
    {
        $port = 1234;
        $logger = $this->createMock(LoggerInterface::class);

        $app = Application::create($port, $logger);

        static::assertEquals($port, $app->getConfiguration()->getPort());
        static::assertEquals($logger, $app->getConfiguration()->getRequestLogger());
    }

    public function testOnServerStartStop(): \Generator
    {
        $s = '';

        $app = Application::create(1234);
        $app->onServerStart(function () use (&$s) {
            $s .= '#';
        })->onServerStop(function () use (&$s) {
            $s .= '!';
        });

        $server = $this->getStubServer();
        $server->attach($app);

        yield $server->start();
        yield $server->stop();

        static::assertEquals('#!', $s);
    }
}
