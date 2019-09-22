<?php

namespace Empress\Routing;

use Amp\Http\Server\RequestHandler;
use Amp\Http\Server\Router;
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
        if (\count($this->routers) === 1) {
            return clone $this->routers[0];
        }

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
        if ($handler instanceof RequestHandler) {
            $router->addRoute($verb, $uri, $handler);

            return;
        }

        $router->addRoute($verb, $uri, new EmpressRequestHandler($handler, $reponseTransformer));
    }
}
