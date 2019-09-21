<?php

namespace Empress\Test\Routing;

use Empress\Routing\RouteConfigurator;
use PHPUnit\Framework\TestCase;
use stdClass;

class RouteConfiguratorTest extends TestCase
{
    public function testNoRoutes()
    {
        $configurator = new RouteConfigurator();

        $this->assertEmpty($configurator->getRoutes());
    }

    public function testNoPrefixSet()
    {
        $configurator = new RouteConfigurator();
        $configurator->get('', '');
        $routes = $configurator->getRoutes();

        $this->assertArrayNotHasKey('prefix', $routes);
    }

    public function testPrefixNotSetWhenNoRoutesPresent()
    {
        $configurator = new RouteConfigurator();
        $configurator->prefix('/prefix', function ($c) {
        });
        $routes = $configurator->getRoutes();

        $this->assertArrayNotHasKey('/prefix', $routes);
    }

    public function testPrefixSet()
    {
        $configurator = new RouteConfigurator();
        $configurator->prefix('/prefix', function ($c) {
            $c->get('', '');
        });
        $routes = $configurator->getRoutes();

        $this->assertArrayHasKey('/prefix', $routes);
    }

    public function testPrefixesAreStacked()
    {
        $configurator = new RouteConfigurator();
        $configurator->prefix('/prefix1', function ($c) {
            $c->prefix('/prefix2', function ($c) {
                $c->get('', '');
            });
        });
        $routes = $configurator->getRoutes();

        $this->assertArrayHasKey('/prefix1/prefix2', $routes);
    }

    public function testPrefixesDoNotLeak()
    {
        $configurator = new RouteConfigurator();
        $configurator->prefix('/prefix1', function ($c) {
            $c->get('', '');

            $c->prefix('/prefix2', function ($c) {
                $c->get('', '');
            });
        });
        $routes = $configurator->getRoutes();

        $this->assertCount(1, $routes['/prefix1']);
        $this->assertCount(1, $routes['/prefix1/prefix2']);
    }

    public function testClosureArgumentIsNotSelf()
    {
        $configurator = new RouteConfigurator();
        $configurator->prefix('/prefix1', function ($c) use(&$configurator) {
            $this->assertNotSame($configurator, $c);
        });

        $configurator->getRoutes();
    }
}
