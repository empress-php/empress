<?php

namespace Empress\Test\Logging;

use Amp\ByteStream\InMemoryStream;
use Amp\Http\Status;
use Amp\PHPUnit\AsyncTestCase;
use Empress\Logging\ResponseStringifier;

class ResponseStringifierTest extends AsyncTestCase
{
    public function testStringify(): \Generator
    {
        $responseBody = new InMemoryStream('');
        $responseStringifier = new ResponseStringifier(Status::OK, [], $responseBody);

        $stringified = yield $responseStringifier->stringify();

        static::assertStringContainsString('200', $stringified);
    }
}
