<?php

require __DIR__ . '/../vendor/autoload.php';

use Amp\Http\Status;
use Amp\Loop;
use Empress\AbstractApplication;
use Empress\Context;
use Empress\Empress;
use Empress\Routing\RouteConfigurator;

class StupidAuthApp extends AbstractApplication
{

    /**
     * @var array
     */
    private $users;

    public function __construct()
    {
        $this->users = [
            'foo' => 'bar',
            'admin' => 'admin',
        ];
    }

    public function configureRoutes(RouteConfigurator $routes): void
    {
        $routes->group('/auth', function (RouteConfigurator $routes) {
            $routes->before(function (Context $ctx) {
                $query = $ctx->queryArray();
                $username = $query['username'] ?? null;
                $password = $query['password'] ?? null;

                if ($username === null || $password === null) {
                    $ctx->halt(Status::BAD_REQUEST, 'Missing username or password');
                }

                if (!isset($this->users[$username])) {
                    $ctx->halt(Status::UNAUTHORIZED, 'User does not exist');
                }

                if ($this->users[$username] !== $password) {
                    $ctx->halt(Status::UNAUTHORIZED, 'Invalid password');
                }
            });

            $routes->get('/hello', function (Context $ctx) {
                $ctx->respond('Hi there!');
            });

            // Not fired if halt() is called anywhere in the before filter
            $routes->after(function (Context $ctx) {
                echo 'After 1' . PHP_EOL;
            });

            $routes->after(function (Context $ctx) {
                echo 'After 2' . PHP_EOL;
            });
        });
    }
}

$empress = new Empress(new StupidAuthApp());
Loop::run([$empress, 'boot']);
