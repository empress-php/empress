<?php

use Amp\ByteStream\InputStream;
use Amp\ByteStream\IteratorStream;
use Amp\Delayed;
use Amp\Producer;
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
        $ctx->response($this->createStream());
    }

    private function createStream(): InputStream
    {
        return new IteratorStream(new Producer(function (callable $emit) {
            for ($i = 0; $i < 10; $i++) {
                yield $emit("Line #$i\n");

                yield new Delayed(500);
            }
        }));
    }
}

$app = Application::create(9010);
$app->routes(new StreamController());

return $app;
