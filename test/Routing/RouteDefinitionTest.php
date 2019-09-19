<?php

namespace Empress\Test\Routing;

use Empress\Routing\RouteDefinition;
use PHPUnit\Framework\TestCase;
use function Empress\Routing\route;

class RouteDefinitionTest extends TestCase
{
    public function testDefinitionsAreEquivalent()
    {
        $shorthandDefinition = route('POST', '/', 'someHandler');
        $definition = new RouteDefinition('POST', '/', 'someHandler');

        $this->assertEquals($definition->getVerb(), $shorthandDefinition->getVerb());
        $this->assertEquals($definition->getUri(), $shorthandDefinition->getUri());
        $this->assertEquals($definition->getHandler(), $shorthandDefinition->getHandler());
    }
}
