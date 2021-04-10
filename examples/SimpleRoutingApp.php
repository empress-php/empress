<?php

use Amp\Loop;
use Empress\Application;
use Empress\Context;
use Empress\Empress;
use Empress\Routing\Routes;

require __DIR__ . '/../vendor/autoload.php';

Loop::run(function () {
    $app = Application::create(9010);

    $app->routes(function (Routes $routes) {
        $routes->group('/hello', function (Routes $routes) {
            $routes->get('/world', function (Context $ctx) {
                $ctx->html('<h1>Hello</h1>');
            });

            $routes->get('/foo/bar', function (Context $ctx) {
                $ctx->response('Foo bar');
            });

            $routes->beforeAt('/foo/*', function (Context $ctx) {
                echo "Before /hello/foo/*\n";
            });

            $routes->afterAt('/world', function (Context $ctx) {
                echo "After /hello/world\n";
            });
        });
    });

    $empress = new Empress($app);

    yield $empress->boot();
});
