<?php

use Amp\Loop;
use Empress\Application;
use Empress\Context;
use Empress\Empress;
use Empress\Routing\RouteCollector\AnnotatedRouteCollectorTrait;
use Empress\Routing\RouteCollector\Attribute\Route;
use Empress\Routing\RouteCollector\RouteCollectorInterface;

require __DIR__ . '/../vendor/autoload.php';

class IndexController implements RouteCollectorInterface
{
    use AnnotatedRouteCollectorTrait;

    #[Route('GET', '/age/:age')]
    public function index(Context $ctx)
    {
        $age = $ctx['age']->to('int')->unwrap();

        $ctx->html('Your age is: ' . $age);
    }
}

$app = Application::create(8001);
$app->routes(new IndexController());

$empress = new Empress($app);

Loop::run([$empress, 'boot']);