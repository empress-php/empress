<?php

namespace Empress\Test;

use Amp\Loop;
use Amp\Promise;
use Amp\Artax\DefaultClient;
use Amp\ByteStream\ResourceOutputStream;
use Amp\Log\StreamHandler;
use Amp\PHPUnit\AsyncTestCase;
use Empress\Empress;
use Monolog\Logger;
use Amp\Http\Server\Request;

class EmpressTest extends AsyncTestCase
{
    private $port;
    private $app;
    private $url;
    private $client;

    public function setUp()
    {
        $stream = new ResourceOutputStream(\STDOUT);
        $logHandler = new StreamHandler($stream);
        $logger = new Logger('empress', [$logHandler]);

        $this->port = 2019;
        $this->app = new Empress([], $logger, $this->port);
        $this->url = 'http://localhost:' . $this->port;
        $this->client = new DefaultClient;

        parent::setUp();
    }

    public function tearDown()
    {
        $this->app->shutDown();

        parent::tearDown();
    }

    public function testPlainTextGetRequest()
    {
        $message = 'Hello, World';
        $this->app->get('/', function () use ($message) {
            return $this['response']->with($message);
        });

        $this->app->run();

        $response = yield $this->request('/');
        $responseBody = yield $response->getBody();

        $this->assertSame($message, $responseBody);
    }

    public function testJsonGetRequest()
    {
        $payload = ['status' => 'ok'];
        $this->app->get('/', function () use ($payload) {
            return $this['json']->with($payload);
        });

        $this->app->run();

        $response = yield $this->request('/');
        $responseBody = yield $response->getBody();

        $this->assertSame($payload, json_decode($responseBody, true));
    }

    public function testJsonGetRequestWithParam()
    {
        $payload = ['response' => 'Hello, Jakob'];
        $this->app->get('/name/{name}', function (Request $request, array $params) {
            return $this['json']->with(['response' => 'Hello, ' . $params['name']]);
        });

        $this->app->run();

        $response = yield $this->request('/name/Jakob');
        $responseBody = yield $response->getBody();

        $this->assertSame($payload, json_decode($responseBody, true));
    }

    private function request(string $url): Promise
    {
        return $this->client->request($this->url . $url);
    }
}
