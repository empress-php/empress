<?php

require __DIR__ . '/../vendor/autoload.php';

use Amp\Http\Status;
use Amp\Loop;
use Empress\AbstractApplication;
use Empress\Context;
use Empress\Empress;
use Empress\Exception\HaltException;
use Empress\Routing\Routes;

class SimpleRoutingApp extends AbstractApplication
{
    public function configureRoutes(Routes $routes): void
    {

        $routes->get('/', function (Context $ctx) {
            throw new HaltException(Status::OK, [], 'Naaay');
            $ctx->halt(Status::OK, 'Helloz');
        });

        $routes->on(HaltException::class, function (Context $ctx) {

            /** @var HaltException $e */
            $e = $ctx->exception();

            $ctx->respond('Halted: ' . yield $e->toResponse()->getBody()->read());
        });
    }
}




$empress = new Empress(new SimpleRoutingApp());
Loop::run([$empress, 'boot']);
