<?php

declare(strict_types=1);

namespace Empress\Test\Functional;

use Amp\Http\Client\Cookie\CookieInterceptor;
use Amp\Http\Client\Cookie\CookieJar;
use Amp\Http\Client\Cookie\InMemoryCookieJar;
use Amp\Http\Client\HttpClient;
use Amp\Http\Client\HttpClientBuilder;
use Amp\Http\Client\Request;
use Amp\PHPUnit\AsyncTestCase;
use Amp\Promise;
use Empress\Application;
use Empress\Empress;
use GuzzleHttp\Psr7\Uri;

abstract class FunctionalTestCase extends AsyncTestCase
{
    private CookieJar $cookieJar;

    private HttpClient $client;

    private Empress $empress;

    protected function setUp(): void
    {
        $this->cookieJar = new InMemoryCookieJar();

        $builder = new HttpClientBuilder();
        $builder->interceptNetwork(new CookieInterceptor($this->cookieJar));

        $this->client = $builder->build();

        parent::setUp();
    }

    protected function setUpAsync(): Promise
    {
        $this->empress = new Empress($this->getApplication());

        return $this->empress->boot();
    }

    protected function tearDownAsync()
    {
        return $this->empress->shutDown();
    }

    protected function request(string $uri, string $method = 'GET', mixed $body = null, array $headers = []): Promise
    {
        $request = new Request($this->getHost() . $uri, $method);

        if (isset($body)) {
            $request->setBody($body);
        }

        if (isset($headers)) {
            $request->setHeaders($headers);
        }

        return $this->client->request($request);
    }

    protected function getCookies(): Promise
    {
        return $this->cookieJar->get(new Uri($this->getHost()));
    }

    abstract protected function getApplication(): Application;

    abstract protected function getHost(): string;
}
