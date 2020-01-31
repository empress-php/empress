<?php

namespace Empress\Test;

use Amp\Http\Server\Session\Session;
use Amp\Http\Server\Session\Storage;
use Amp\PHPUnit\AsyncTestCase;
use Empress\Request;

class RequestContextTest extends AsyncTestCase
{
    use HelperTrait;

    public function testGetParams()
    {
        $params = [
            'hello' => 'world',
            'param1' => 'val1',
        ];

        $httpRequest = $this->createMockRequest('GET', '/', $params);
        $context = new Request($httpRequest);

        $this->assertEquals($params, $context->getParams());
    }

    public function testGetParam()
    {
        $params = [
            'id' => 1,
            'weight' => 20,
        ];

        $httpRequest = $this->createMockRequest('GET', '/', $params);
        $context = new Request($httpRequest);

        $this->assertEquals(1, $context->getParam('id'));
        $this->assertEquals(20, $context->getParam('weight'));
    }

    public function testGetSession()
    {
        $mockStorage = $this->createMock(Storage::class);
        $session = new Session($mockStorage, '');

        $httpRequest = $this->createMockRequest('GET', '/');
        $httpRequest->setAttribute(Session::class, $session);

        $context = new Request($httpRequest);

        $this->assertEquals($session, $context->getSession());
    }

    public function testGetRequest()
    {
        $httpRequest = $this->createMockRequest('GET', '/');
        $client = $httpRequest->getClient();

        $context = new Request($httpRequest);

        $this->assertEquals($client, $context->getRequest()->getClient());
    }
}
