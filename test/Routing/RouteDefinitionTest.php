<?php

namespace Empress\Test\Routing;

use Empress\Routing\RouteDefinition;
use PHPUnit\Framework\TestCase;

class RouteDefinitionTest extends TestCase
{
    public function testVerbIsCapitalized()
    {
        $route = new RouteDefinition('post', '/', 'someHandler');

        $this->assertEquals('POST', $route->getVerb());
    }
}
