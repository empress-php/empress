<?php

namespace Empress\Routing;

use Doctrine\Common\Annotations\AnnotationReader;
use Empress\Exception\RouteException;
use Empress\Routing\Annotation\Mount;
use Empress\Routing\Annotation\Route;
use ReflectionClass;
use ReflectionMethod;

/**
 * Allows for associating controllers with routes using class and method-level annotations.
 */
class ControllerMounter
{
    private const HTTP_METHODS = [
        'GET',
        'POST',
        'PUT',
        'PATCH',
        'HEAD',
        'OPTIONS',
        'DELETE',
    ];

    /**
     * @var object
     */
    private $controller;

    /**
     * @var RouteConfigurator
     */
    private $routeConfigurator;

    /**
     * ControllerMounter constructor.
     * @param object $controller
     * @throws RouteException
     */
    public function __construct(object $controller)
    {
        $this->controller = $controller;
        $this->routeConfigurator = new RouteConfigurator();

        $this->mount();
    }

    private function mount(): void
    {
        $controllerReflector = new ReflectionClass(\get_class($this->controller));
        $controllerMethods = \array_filter($controllerReflector->getMethods(ReflectionMethod::IS_PUBLIC), function ($method) {

            // Skip constructors, destructors and magic methods
            return \strpos($method->getName(), '__') !== 0;
        });

        $reader = new AnnotationReader();
        $mountAnnotation = $reader->getClassAnnotation($controllerReflector, Mount::class);

        if (\is_null($mountAnnotation)) {
            throw new RouteException('Mount annotation missing from controller definition');
        }

        $prefix = \rtrim($mountAnnotation->path, '/');

        foreach ($controllerMethods as $controllerMethod) {
            $routeAnnotation = $reader->getMethodAnnotation($controllerMethod, Route::class);

            if (\is_null($routeAnnotation)) {
                throw new RouteException('Public controller methods must be annotated with a route annotation');
            }

            $verb = $routeAnnotation->method;

            if (!\in_array($v = \strtoupper($verb), self::HTTP_METHODS)) {
                throw new RouteException(\sprintf('Unknown HTTP method: %s', $v));
            }

            $fullPath = $prefix . '/' . \ltrim($routeAnnotation->path, '/');

            $transformer = isset($routeAnnotation->transform) ? new $routeAnnotation->transform : null;

            $this->routeConfigurator->{\strtolower($verb)}(
                $fullPath,
                $controllerMethod->getClosure($this->controller),
                $transformer
            );
        }
    }

    public function getRoutes(): array
    {
        return $this->routeConfigurator->getRoutes();
    }
}
