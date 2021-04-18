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


#[Group('/say')]
class IndexController implements RouteCollectorInterface
{
    private static int $count = 0;

    use AnnotatedRouteCollectorTrait;

    #[Route('GET', '/hello')]
    public function index(Context $ctx)
    {
        $ctx->html('Hello World!');
    }

    #[Route('AFTER', '/*')]
    public function afterIndex()
    {
        \printf("After %d reqs\n", ++self::$count);
    }
}

Loop::run(function () {
    $app = Application::create(9010);
    $app->routes(new IndexController());

    $empress = new Empress($app);

    yield $empress->boot();
});
