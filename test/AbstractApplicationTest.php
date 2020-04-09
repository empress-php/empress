<?php

namespace Empress\Test;

use Amp\PHPUnit\AsyncTestCase;
use Amp\Success;
use Empress\AbstractApplication;
use Empress\Configuration\ApplicationConfiguration;
use Empress\Routing\Routes;

class AbstractApplicationTest extends AsyncTestCase
{
    use HelperTrait;

    /**
     * @var AbstractApplication
     */
    private $app;

    public function setUp(): void
    {
        $this->app = $this->createApplication();

        parent::setUp();
    }

    public function testOnStart()
    {
        $server = $this->createMockServer();
        $this->assertInstanceOf(Success::class, $this->app->onStart($server));
    }

    public function testOnStop()
    {
        $server = $this->createMockServer();
        $this->assertInstanceOf(Success::class, $this->app->onStop($server));
    }
}
