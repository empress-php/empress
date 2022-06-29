<?php

declare(strict_types=1);

namespace Empress\Test\Functional;

use Amp\Http\Client\Response;
use Empress\Application;
use Empress\ConfigurationBuilder;
use Empress\Routing\Routes;

final class StaticContentTest extends FunctionalTestCase
{
    private const PORT = 1234;

    public function testGetTextFile(): \Generator
    {

        /** @var Response $response */
        $response = yield $this->request('/abc.txt');

        $body = yield $response->getBody()->buffer();

        self::assertSame('Hello World!', $body);
    }

    protected function getApplication(): Application
    {
        $configurationBuilder = $this->getConfigurationBuilder();

        $configuration = $configurationBuilder
            ->withStaticContentPath(__DIR__ . '/Resources')
            ->build();

        $app = Application::create(1234, $configuration);

        $app->routes(function (Routes $routes): void {
            $routes->get('/', fn () => null);
        });

        return $app;
    }

    protected function getHost(): string
    {
        return 'http://0.0.0.0:' . self::PORT;
    }
}
