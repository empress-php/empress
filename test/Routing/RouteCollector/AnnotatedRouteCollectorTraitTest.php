<?php

namespace Empress\Test\Routing\RouteCollector;

use Empress\Application;
use Empress\Routing\Handler\HandlerType;
use Empress\Routing\RouteCollector\AnnotatedRouteCollectorTrait;
use Empress\Routing\RouteCollector\Attribute\Group;
use Empress\Routing\RouteCollector\Attribute\Route;
use Empress\Routing\Routes;
use PHPUnit\Framework\TestCase;

class AnnotatedRouteCollectorTraitTest extends TestCase
{
    public function testSimpleRoute(): void
    {
        $application = Application::create(8000);
        $collector = new class {
            use AnnotatedRouteCollectorTrait;

            #[Route('GET', '/')]
            public function index()
            {
            }
        };

        $application->routes($collector);

        $application->routes(function (Routes $routes): void {
            $collection = $routes->getHandlerCollection();
            $entry = $collection->first();

            static::assertEquals(HandlerType::GET, $entry?->getType());
            static::assertEquals('/', (string) $entry?->getPath());
        });
    }

    public function testRegisterManyRoutesForOneHandler(): void
    {
        $application = Application::create(8000);
        $collector = new class {
            use AnnotatedRouteCollectorTrait;

            #[Route('GET', '/')]
            #[Route('GET', '/index')]
            #[Route('POST', '/index')]
            public function index()
            {
            }
        };

        $application->routes($collector);

        $application->routes(function (Routes $routes): void {
            $collection = $routes->getHandlerCollection();

            static::assertEquals(
                2,
                $collection->filterByType(HandlerType::GET)->count()
            );

            static::assertEquals(
                2,
                $collection->filterByPath('/index')->count()
            );
        });
    }

    public function testCollectorWithGroup(): void
    {
        $application = Application::create(8000);
        $collector = new #[Group('/group')] class
        {
            use AnnotatedRouteCollectorTrait;

            #[Route('GET', '/index')]
            public function index()
            {
            }
        };

        $application->routes($collector);

        $application->routes(function (Routes $routes): void {
            $collection = $routes->getHandlerCollection();
            $entry = $collection->first();

            static::assertEquals('/group/index', $entry?->getPath());
        });
    }
}
