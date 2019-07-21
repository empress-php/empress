<?php

namespace Empress\Test;

use Amp\Loop;
use Amp\Promise;
use Amp\Http\Server\Request;
use Amp\Http\Server\Response;
use Amp\Artax\DefaultClient;
use Amp\PHPUnit\AsyncTestCase;
use Empress\Empress;

class EmpressTest extends AsyncTestCase
{
    private $port;
    private $app;
    private $url;
    private $client;

    public function setUp()
    {
        $this->port = 2019;
        $this->app = new Empress($this->port);
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
        $this->app->get('/', function ($req, $res, $params) use ($message) {
            $res->setBody($message);

            return $res;
        });

        $this->app->run();

        $response = yield $this->request('/');
        $responseBody = yield $response->getBody();

        $this->assertSame($message, $responseBody);
    }

    public function testCannotAddRoutesOnceRunning()
    {
        $this->expectException(\Error::class);

        $this->app->run();

        $this->app->get(function () { });
    }

    public function testJsonGetRequest()
    {
        $payload = ['status' => 'ok'];
        $this->app->get('/', function (Request $req, Response $res, array $params) use ($payload) {
            $res->setHeader('content-type', 'application/json');
            $res->setBody(json_encode($payload));

            return $res;
        });

        $this->app->run();

        $response = yield $this->request('/');
        $responseBody = yield $response->getBody();

        $this->assertSame($payload, json_decode($responseBody, true));
    }

    public function testJsonGetRequestWithParam()
    {
        $payload = ['response' => 'Hello, Jakob'];
        $this->app->get('/name/{name}', function (Request $req, Response $res, array $params) {
            $res->setHeader('content-type', 'application/json');
            $res->setBody(json_encode(['response' => 'Hello, ' . $params['name']]));

            return $res;
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
