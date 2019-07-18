<?php

namespace Empress\Test\Services;

use Amp\PHPUnit\AsyncTestCase;
use Pimple\Container;
use Amp\Http\Status;
use Empress\Services\ResponseService;

class ResponseServiceTest extends AsyncTestCase
{
    public function testWith()
    {
        $status = Status::OK;
        $payload = 'Hello World';

        $service = new ResponseService;
        $response = $service->with($payload, [], $status);
        $returnedPayload = yield $response->getBody()->read();

        $this->assertSame($status, $response->getStatus());
        $this->assertSame($payload, $returnedPayload);
    }

    public function testGetHeaders()
    {
        $headers = [
            'content-type' => 'application/json',
        ];

        $service = new ResponseService;
        $response = $service->with('', $headers);

        $this->assertSame(['application/json'], $response->getHeaderArray('content-type'));
    }
}
