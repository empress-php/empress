<?php

namespace Empress\Test\Functional;

use Amp\Http\Client\Response;
use Amp\Http\Status;
use Empress\Application;
use Empress\Context;
use Empress\Routing\Routes;

class StatusMappersTest extends FunctionalTestCase
{
    private const PORT = 1234;

    public function testHtmlStatusMapper(): \Generator
    {

        /** @var Response $response */
        $response = yield $this->request('/', 'GET', null, ['Accept' => 'text/html']);

        $body = yield $response->getBody()->buffer();

        static::assertEquals('<h1>Not found</h1>', $body);
    }

    public function testJsonStatusMapper(): \Generator
    {

        /** @var Response $response */
        $response = yield $this->request('/', 'GET', null, ['Accept' => 'application/json']);

        $body = yield $response->getBody()->buffer();
        $json = \json_decode($body, true);

        static::assertEquals([
            'status' => 'Not found'
        ], $json);
    }

    protected function getApplication(): Application
    {
        $app = Application::create(self::PORT);

        $app->status(Status::NOT_FOUND, function (Context $ctx) {
            $ctx->html('<h1>Not found</h1>');
        }, ['Accept' => 'text/html']);

        $app->status(Status::NOT_FOUND, function (Context $ctx) {
            $ctx->json(['status' => 'Not found']);
        }, ['Accept' => 'application/json']);

        $app->routes(function (Routes $routes) {
            $routes->get('/', fn (Context $ctx) => $ctx->status(Status::NOT_FOUND));
        });

        return $app;
    }

    protected function getHost(): string
    {
        return 'http://0.0.0.0:' . self::PORT;
    }
}
