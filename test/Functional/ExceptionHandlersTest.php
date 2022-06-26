<?php

declare(strict_types=1);

namespace Empress\Test\Functional;

use Amp\Http\Client\Response;
use Amp\Http\Status;
use Empress\Application;
use Empress\Context;
use Empress\Routing\Routes;

final class ExceptionHandlersTest extends FunctionalTestCase
{
    private const PORT = 1234;

    public function testExceptionHandler(): \Generator
    {

        /** @var Response $response */
        $response = yield $this->request('/');

        $body = yield $response->getBody()->buffer();
        $json = \json_decode($body, true);

        self::assertSame([
            'status' => Status::INTERNAL_SERVER_ERROR,
            'message' => 'An exception happened',
        ], $json);
    }

    protected function getApplication(): Application
    {
        $app = Application::create(self::PORT);

        $app->exception(\Exception::class, function (Context $ctx, \Exception $e): void {
            $ctx->json([
                'status' => Status::INTERNAL_SERVER_ERROR,
                'message' => $e->getMessage(),
            ]);
        });

        $app->routes(function (Routes $routes): void {
            $routes->get('/', function (): void {
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
