<?php

declare(strict_types=1);

namespace Empress\Test\Functional;

use Amp\Http\Client\Response;
use Amp\Http\Server\Options;
use Empress\Application;
use Empress\Context;
use Empress\Routing\Routes;
use Empress\Sse\SseClient;

final class SseTest extends FunctionalTestCase
{
    private const PORT = 8001;

    public function testComment(): \Generator
    {
        /** @var Response $response */
        $response = yield $this->request('/sse-comment');
        $body = yield $response->getBody()->buffer();

        self::assertSame("A comment\n\n", $body);
    }

    public function testData(): \Generator
    {
        /** @var Response $response */
        $response = yield $this->request('/sse-data');
        $body = yield $response->getBody()->buffer();

        self::assertEquals("data: First line\ndata: Second line\n\n", $body);
    }

    public function testEvent()
    {
        /** @var Response $response */
        $response = yield $this->request('/sse-event');
        $body = yield $response->getBody()->buffer();

        self::assertSame("event: some event\ndata: event payload\ndata: next line\n\n", $body);
    }

    protected function getApplication(): Application
    {
        $builder = $this->getConfigurationBuilder();
        $options = (new Options())
            ->withoutCompression();

        $config = $builder
            ->withServerOptions($options)
            ->build();

        $app = Application::create(8001, $config);

        $app->routes(function (Routes $routes): void {
            $routes->get('/sse-comment', function (Context $ctx): void {
                $ctx->sse(function (SseClient $client) {
                    yield $client->comment('A comment');
                });
            });

            $routes->get('/sse-data', function (Context $ctx): void {
                $ctx->sse(function (SseClient $client) {
                    yield $client->data("First line\nSecond line");
                });
            });

            $routes->get('/sse-event', function (Context $ctx): void {
                $ctx->sse(function (SseClient $client) {
                    yield $client->event('some event', "event payload\nnext line");
                });
            });
        });

        return $app;
    }

    protected function getHost(): string
    {
        return 'http://0.0.0.0:' . self::PORT;
    }
}
