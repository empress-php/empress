<?php

use Amp\Loop;
use Empress\Application;
use Empress\Context;
use Empress\Empress;
use Empress\Routing\RouteCollector\AnnotatedRouteCollectorTrait;
use Empress\Routing\RouteCollector\Attribute\Group;
use Empress\Routing\RouteCollector\Attribute\Route;
use Empress\Routing\RouteCollector\RouteCollectorInterface;

require __DIR__ . '/../vendor/autoload.php';


#[Group('/index')]
class IndexController implements RouteCollectorInterface
{
    use AnnotatedRouteCollectorTrait;

    #[Route('GET', '/')]
    public function index(Context $ctx)
    {
        $ctx->response('<h1>Hello!</h1>');
    }
}

Loop::run(function () {
    $app = Application::create(9010);
    $app->routes(new IndexController());

    $empress = new Empress($app);

    yield $empress->boot();
});
