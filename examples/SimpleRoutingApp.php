<?php

require __DIR__ . '/../vendor/autoload.php';

use Amp\Loop;
use Empress\AbstractApplication;
use Empress\Context;
use Empress\Empress;
use Empress\Routing\RouteConfigurator;

class SimpleRoutingApp extends AbstractApplication
{
    public function configureRoutes(RouteConfigurator $routes): void
    {
        $routes->get('/', function (Context $ctx) {
            $ctx->respond('Hello, World!');
        });
    }
}

$empress = new Empress(new SimpleRoutingApp());
Loop::run([$empress, 'boot']);
