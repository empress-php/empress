<?php

namespace Empress\Test;

use Amp\PHPUnit\AsyncTestCase;
use Empress\Empress;
use Empress\Exception\StartupException;

class EmpressTest extends AsyncTestCase
{
    use HelperTrait;

    public function testBootWithoutRoutes()
    {
        $this->expectException(StartupException::class);

        $empress = new Empress($this->createApplication());

        yield $empress->boot();
    }
}
