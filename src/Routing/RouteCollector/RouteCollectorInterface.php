<?php

namespace Empress\Routing\RouteCollector;

use Empress\Routing\Routes;

interface RouteCollectorInterface
{
    public function __invoke(Routes $routes): void;
}
