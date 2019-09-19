<?php

namespace Empress\Test\Routing;

use Empress\Routing\ControllerDefinition;
use PHPUnit\Framework\TestCase;
use function Empress\Routing\controller;
use function Empress\Routing\route;

class ControllerDefinitionTest extends TestCase
{
    public function testDefinitionsAreEquivalent()
    {
        $routes = [
            route('GET', '/', 'handler1'),
            route('POST', '/abc', 'handler2')
        ];

        $shorthandDefinition = controller(
            'IndexController',
            ...$routes
        );
        $definition = new ControllerDefinition('IndexController', ...$routes);

        $this->assertEquals($definition->getRouterPrefix(), $shorthandDefinition->getRouterPrefix());
        $this->assertEquals($definition->getRouteDefinitions(), $shorthandDefinition->getRouteDefinitions());
    }

    /** @dataProvider provideControllerNames */
    public function testRouterPrefix(string $controllerName, string $processedName)
    {
        $definition = new ControllerDefinition($controllerName);

        $this->assertEquals($processedName, $definition->getRouterPrefix());
    }

    public function provideControllerNames()
    {
        return [
            ['BooksController', 'books'],
            ['ServiceProviderController', 'service-provider'],
            ['UserStoreController', 'user-store'],
        ];
    }
}
