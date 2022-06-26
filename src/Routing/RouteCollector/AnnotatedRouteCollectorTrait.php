<?php

declare(strict_types=1);

namespace Empress\Routing\RouteCollector;

use Empress\Routing\Handler\HandlerType;
use Empress\Routing\RouteCollector\Attribute\Group;
use Empress\Routing\RouteCollector\Attribute\Route;
use Empress\Routing\Routes;
use ReflectionAttribute;
use ReflectionClass;
use ReflectionMethod;

trait AnnotatedRouteCollectorTrait
{
    public function __invoke(Routes $routes): void
    {
        $processRoutes = function (Routes $routes, array $methods): void {
            foreach ($methods as $method) {
                $attributes = $method->getAttributes(Route::class);

                foreach ($attributes as $attribute) {

                    /** @var Route $attributeInstance */
                    $attributeInstance = $attribute->newInstance();
                    $type = HandlerType::fromString($attributeInstance->getType());

                    $routes->addEntry($type, $attributeInstance->getPath(), $method->getClosure($this));
                }
            }
        };

        $class = new ReflectionClass(static::class);
        $methods = $class->getMethods(ReflectionMethod::IS_PUBLIC);

        $attributes = $class->getAttributes(Group::class);

        /** @var ReflectionAttribute|null $attribute */
        $attribute = $attributes[0] ?? null;

        if ($attribute !== null) {

            /** @var Group $groupAttribute */
            $groupAttribute = $attribute->newInstance();
            $prefix = $groupAttribute->getPrefix();

            $routes->group($prefix, fn (Routes $routes) => $processRoutes($routes, $methods));

            return;
        }

        $processRoutes($routes, $methods);
    }
}
