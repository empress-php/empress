<?php

namespace Empress\Test\Routing;

use Empress\Routing\RouteDefinition;
use Empress\Transformer\ResponseTransformerInterface;
use PHPUnit\Framework\TestCase;

class RouteDefinitionTest extends TestCase
{
    public function testVerbIsCapitalized()
    {
        $route = new RouteDefinition('post', '/', 'someHandler');

        $this->assertEquals('POST', $route->getVerb());
    }

    public function testResponseTransformerIsSet()
    {
        $route = new RouteDefinition('', '', '');
        $responseTransformer = $this->createMock(ResponseTransformerInterface::class);
        $route->setResponseTransformer($responseTransformer);

        $this->assertEquals($responseTransformer, $route->getResponseTransformer());
    }
}
