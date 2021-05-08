<?php

namespace Empress\Test\Functional;

use Amp\Http\Client\Response;
use Amp\Http\Status;
use Empress\Application;
use Empress\Context;
use Empress\Routing\Routes;

class ExceptionHandlersTest extends FunctionalTestCase
{
    private const PORT = 1234;

    public function testExceptionHandler(): \Generator
    {

        /** @var Response $response */
        $response = yield $this->request('/');

        $body = yield $response->getBody()->buffer();
        $json = \json_decode($body, true);

        static::assertEquals([
            'status' => Status::INTERNAL_SERVER_ERROR,
            'message' => 'An exception happened'
        ], $json);
    }

    protected function getApplication(): Application
    {
        $app = Application::create(self::PORT);

        $app->exception(\Exception::class, function (Context $ctx, \Exception $e) {
            $ctx->json([
                'status' => Status::INTERNAL_SERVER_ERROR,
                'message' => $e->getMessage()
            ]);
        });

        $app->routes(function (Routes $routes) {
            $routes->get('/', function () {
                throw new \Exception('An exception happened');
            });
        });

        return $app;
    }

    protected function getHost(): string
    {
        return 'http://0.0.0.0:' . self::PORT;
    }
}
