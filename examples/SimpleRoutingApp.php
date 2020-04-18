<?php

use Amp\Loop;
use Empress\Application;
use Empress\Context;
use Empress\Empress;
use Empress\Routing\Routes;

require __DIR__ . '/../vendor/autoload.php';

Loop::run(function () {
    $app = new Application();
    $routes = $app->routes();

    $routes->group('/hello', function (Routes $routes) {
        $routes->get('/world', function (Context $ctx) {
            $ctx->respond('Hello');

            throw new Exception('Biyotch');
        });

        $routes->get('/foo/bar', function (Context $ctx) {
            $ctx->respond('Foo bar');
        });

        $routes->beforeAt('/foo/*', function (Context $ctx) {
            echo "Before /hello/foo/*\n";
        });

        $routes->afterAt('/world', function (Context $ctx) {
            echo "After /hell/world\n";
        });
    });

    $app->exception(Exception::class, function (Context $ctx) {
        echo "An exception happened: {$ctx->exception()->getMessage()}";

        $ctx->rethrow();
    });

    $empress = new Empress($app);

    yield $empress->boot();
});
