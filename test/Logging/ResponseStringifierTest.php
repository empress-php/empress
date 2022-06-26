<?php

declare(strict_types=1);

namespace Empress\Test\Logging;

use Amp\ByteStream\InMemoryStream;
use Amp\Http\Status;
use Amp\PHPUnit\AsyncTestCase;
use Empress\Logging\ResponseStringifier;

final class ResponseStringifierTest extends AsyncTestCase
{
    public function testStringify(): \Generator
    {
        $responseBody = new InMemoryStream('');
        $responseStringifier = new ResponseStringifier(Status::OK, [], $responseBody);

        $stringified = yield $responseStringifier->stringify();

        self::assertStringContainsString('200', $stringified);
    }
}
