<?php

declare(strict_types=1);

namespace Empress\Test\Routing\RouteCollector;

use Empress\Application;
use Empress\Routing\Handler\HandlerType;
use Empress\Routing\RouteCollector\AnnotatedRouteCollectorTrait;
use Empress\Routing\RouteCollector\Attribute\Group;
use Empress\Routing\RouteCollector\Attribute\Route;
use Empress\Routing\Routes;
use PHPUnit\Framework\TestCase;

final class AnnotatedRouteCollectorTraitTest extends TestCase
{
    public function testSimpleRoute(): void
    {
        $application = Application::create(8000);
        $collector = new class() {
            use AnnotatedRouteCollectorTrait;

            #[Route('GET', '/')]
            public function index(): void
            {
            }
        };

        $application->routes($collector);

        $application->routes(function (Routes $routes): void {
            $collection = $routes->getHandlerCollection();
            $entry = $collection->first();

            self::assertSame(HandlerType::GET, $entry?->getType());
            self::assertSame('/', (string) $entry?->getPath());
        });
    }

    public function testRegisterManyRoutesForOneHandler(): void
    {
        $application = Application::create(8000);
        $collector = new class() {
            use AnnotatedRouteCollectorTrait;

            #[Route('GET', '/')]
            #[Route('GET', '/index')]
            #[Route('POST', '/index')]
            public function index(): void
            {
            }
        };

        $application->routes($collector);

        $application->routes(function (Routes $routes): void {
            $collection = $routes->getHandlerCollection();

            self::assertSame(
                2,
                $collection->filterByType(HandlerType::GET)->count()
            );

            self::assertSame(
                2,
                $collection->filterByPath('/index')->count()
            );
        });
    }

    public function testCollectorWithGroup(): void
    {
        $application = Application::create(8000);
        $collector = new #[Group('/group')] class {
            use AnnotatedRouteCollectorTrait;

            #[Route('GET', '/index')]
            public function index(): void
            {
            }
        };

        $application->routes($collector);

        $application->routes(function (Routes $routes): void {
            $collection = $routes->getHandlerCollection();
            $entry = $collection->first();

            self::assertSame('/group/index', (string) $entry?->getPath());
        });
    }
}
