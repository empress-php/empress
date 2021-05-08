<?php

namespace Empress\Test\Functional;

use Amp\Http\Client\Response;
use Empress\Application;
use Empress\Configuration;
use Empress\ConfigurationBuilder;
use Empress\Routing\Routes;

class StaticContentTest extends FunctionalTestCase
{
    private const PORT = 1234;

    public function testGetTextFile(): \Generator
    {

        /** @var Response $response */
        $response = yield $this->request('/abc.txt');

        $body = yield $response->getBody()->buffer();

        static::assertEquals('Hello World!', $body);
    }

    protected function getApplication(): Application
    {
        $configuration = (new ConfigurationBuilder())
            ->withStaticContentPath(__DIR__ . '/Resources')
            ->build();

        $app = Application::create(1234, $configuration);

        $app->routes(function (Routes $routes) {
            $routes->get('/', fn () => null);
        });

        return $app;
    }

    protected function getHost(): string
    {
        return 'http://0.0.0.0:' . self::PORT;
    }
}
