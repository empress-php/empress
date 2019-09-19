<?php

namespace Empress\Routing;

use Empress\Internal\RequestHandler;

use Amp\File\Driver;
use Amp\Http\Server\Router;
use Amp\Http\Server\StaticContent\DocumentRoot;
use Empress\ResponseTransformerInterface;
use Psr\Container\ContainerInterface;

class RouterBuilder
{

    /** @var \Amp\Http\Server\Router[] */
    private $routers = [];

    /** @var \Psr\Container\ContainerInterface */
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->mainRouter = new Router();
    }

    public function routes(ControllerDefinition ...$controllerDefinitions): void
    {
        foreach ($controllerDefinitions as $controllerDefinition) {
            if (($prefix = $controllerDefinition->getRouterPrefix()) !== '') {
                $router = new Router();
                $router->prefix($prefix);
            } else {
                $router = $this->mainRouter;
            }

            $controllerResponseTransformer = $controllerDefinition->getResponseTransformer();

            /** @var \Empress\Routing\RouteDefinition $routeDefinition */
            foreach ($controllerDefinition->getRouteDefinitions() as $routeDefinition) {
                $responseTransformer = $controllerResponseTransformer ?? $routeDefinition->getResponseTransformer();
                $this->registerCallableHandler(
                    $routeDefinition->getVerb(),
                    $routeDefinition->getUri(),
                    $routeDefinition->getHandler(),
                    $router,
                    $responseTransformer
                );
            }
        }
    }

    public function serveStaticContent(string $root, Driver $filesystem = null): self
    {
        $documentRoot = new DocumentRoot($root, $filesystem);
        $this->mainRouter->setFallback($documentRoot);

        return $this;
    }

    public function getRouter(): Router
    {
        $mergedRouter = clone $this->mainRouter;

        foreach ($this->routers as $router) {
            $mergedRouter->merge($router);
        }

        return $mergedRouter;
    }

    private function registerCallableHandler(string $verb, string $uri, $handler, Router $router, ResponseTransformerInterface $reponseTransformer = null): void
    {
        if (is_array($handler) && $router !== $this->mainRouter) {
            [$class, $method] = $handler;
            $service = $this->container->get($class);
            $handler = [$service, $method];
            $closure = \Closure::fromCallable($handler);
            $router->addRoute($verb, $uri, new RequestHandler($closure, $reponseTransformer));

            return;
        }

        $closure = \Closure::fromCallable($handler);
        $router->addRoute($verb, $uri, new RequestHandler($closure, $reponseTransformer, $this->container));
    }
}
