<?php

use Empress\Application;
use Empress\Context;
use Empress\Routing\RouteCollector\AnnotatedRouteCollectorTrait;
use Empress\Routing\RouteCollector\Attribute\Group;
use Empress\Routing\RouteCollector\Attribute\Route;

require __DIR__ . '/../vendor/autoload.php';


#[Group('/say')]
class IndexController
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

$app = Application::create(9010);
$app->routes(new IndexController());

return $app;
