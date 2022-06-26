<?php

declare(strict_types=1);

namespace Empress\Test\Functional;

use Amp\Http\Client\Response;
use Amp\Http\Status;
use Empress\Application;
use Empress\Context;
use Empress\Routing\Routes;

final class StatusMappersTest extends FunctionalTestCase
{
    private const PORT = 1234;

    public function testHtmlStatusMapper(): \Generator
    {

        /** @var Response $response */
        $response = yield $this->request('/', 'GET', null, ['Accept' => 'text/html']);

        $body = yield $response->getBody()->buffer();

        self::assertSame('<h1>Not found</h1>', $body);
    }

    public function testJsonStatusMapper(): \Generator
    {

        /** @var Response $response */
        $response = yield $this->request('/', 'GET', null, ['Accept' => 'application/json']);

        $body = yield $response->getBody()->buffer();
        $json = \json_decode($body, true);

        self::assertSame([
            'status' => 'Not found',
        ], $json);
    }

    protected function getApplication(): Application
    {
        $app = Application::create(self::PORT);

        $app->status(Status::NOT_FOUND, function (Context $ctx): void {
            $ctx->html('<h1>Not found</h1>');
        }, ['Accept' => 'text/html']);

        $app->status(Status::NOT_FOUND, function (Context $ctx): void {
            $ctx->json(['status' => 'Not found']);
        }, ['Accept' => 'application/json']);

        $app->routes(function (Routes $routes): void {
            $routes->get('/', fn (Context $ctx) => $ctx->status(Status::NOT_FOUND));
        });

        return $app;
    }

    protected function getHost(): string
    {
        return 'http://0.0.0.0:' . self::PORT;
    }
}
