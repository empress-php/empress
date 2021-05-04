<?php

use Amp\ByteStream\ResourceInputStream;
use Empress\Application;
use Empress\Context;
use Empress\Routing\RouteCollector\AnnotatedRouteCollectorTrait;
use Empress\Routing\RouteCollector\Attribute\Group;
use Empress\Routing\RouteCollector\Attribute\Route;

require __DIR__ . '/../vendor/autoload.php';


#[Group('/stream')]
class StreamController
{
    use AnnotatedRouteCollectorTrait;

    #[Route('GET', '/')]
    public function index(Context $ctx)
    {
        $ctx->response($this->getStreamForVideo());
    }

    private function getStreamForVideo(): ResourceInputStream
    {
        return new ResourceInputStream(\fopen('http://commondatastorage.googleapis.com/gtv-videos-bucket/sample/ForBiggerBlazes.mp4', 'rb'));
    }
}

$app = Application::create(9010);
$app->routes(new StreamController());

return $app;
