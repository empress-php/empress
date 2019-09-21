<?php

namespace Empress\Routing;

use Amp\Http\Server\RequestHandler;
use Amp\Http\Server\Router;
use Empress\Exception\RouterBuilderException;
use Empress\Internal\RequestHandler as EmpressRequestHandler;
use Empress\ResponseTransformerInterface;

class RouterBuilder
{

    /** @var \Amp\Http\Server\Router[] */
    private $routers = [];

    /** @var \Empress\Routing\RouteConfigurator */
    private $routeConfigurator;

    public function __construct(RouteConfigurator $routeConfigurator)
    {
        $this->routeConfigurator = $routeConfigurator;
        $this->buildRoutes();
    }

    public function getRouter(): Router
    {
        $routers = $this->routers;
        $acc = new Router();

        foreach ($routers as $router) {
            $acc->merge($router);
        }

        return $acc;
    }

    private function buildRoutes(): void
    {
        $routes = $this->routeConfigurator->getRoutes();

        foreach ($routes as $prefix => $definitions) {
            $router = new Router;
            $router->prefix($prefix);
            $this->routers[] = $router;

            /** @var \Empress\Routing\RouteDefinition $definition */
            foreach ($definitions as $definition) {
                $this->registerHandler(
                    $definition->getVerb(),
                    $definition->getUri(),
                    $definition->getHandler(),
                    $router,
                    $definition->getResponseTransformer()
                );
            }
        }
    }

    private function registerHandler(string $verb, string $uri, $handler, Router $router, ResponseTransformerInterface $reponseTransformer = null): void
    {
        if (\is_callable($handler)) {
            $closure = ($handler instanceof \Closure) ? $handler : \Closure::fromCallable($handler);
            $router->addRoute($verb, $uri, new EmpressRequestHandler($closure, $reponseTransformer));
        } elseif ($handler instanceof RequestHandler) {
            $router->addRoute($verb, $uri, $handler);
        } else {
            throw new RouterBuilderException('Invalid handler type');
        }
    }
}
