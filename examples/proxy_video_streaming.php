<?php

use Amp\ByteStream\ResourceInputStream;
use Amp\Loop;
use Empress\Application;
use Empress\Context;
use Empress\Empress;
use Empress\Routing\RouteCollector\AnnotatedRouteCollectorTrait;
use Empress\Routing\RouteCollector\Attribute\Group;
use Empress\Routing\RouteCollector\Attribute\Route;
use Empress\Routing\RouteCollector\RouteCollectorInterface;

require __DIR__ . '/../vendor/autoload.php';


#[Group('/stream')]
class StreamController implements RouteCollectorInterface
{
    use AnnotatedRouteCollectorTrait;

    #[Route('GET', '/')]
    public function index(Context $ctx)
    {
        $ctx->response($this->getStreamForVideo());
    }

    private function getStreamForVideo(): ResourceInputStream
    {
        return new ResourceInputStream(fopen('http://commondatastorage.googleapis.com/gtv-videos-bucket/sample/ForBiggerBlazes.mp4', 'rb'));
    }
}

Loop::run(function () {
    $app = Application::create(9010);
    $app->routes(new StreamController());

    $empress = new Empress($app);

    yield $empress->boot();
});
