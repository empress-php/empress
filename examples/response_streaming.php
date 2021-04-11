<?php

use Amp\ByteStream\InputStream;
use Amp\ByteStream\IteratorStream;
use Amp\Delayed;
use Amp\Loop;
use Amp\Producer;
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

Loop::run(function () {
    $app = Application::create(9010);
    $app->routes(new StreamController());

    $empress = new Empress($app);

    yield $empress->boot();
});
