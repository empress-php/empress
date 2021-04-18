<?php

namespace Empress\Test\Logging;

use Amp\ByteStream\InMemoryStream;
use Amp\Http\Server\RequestBody;
use Amp\PHPUnit\AsyncTestCase;
use Empress\Logging\RequestStringifier;
use Empress\Logging\StringifierInterface;

class RequestStringifierTest extends AsyncTestCase
{
    public function testStringify(): \Generator
    {
        $method = 'GET';
        $path = '/foo/bar';
        $headers = [
            'X-Foo' => ['!@#', '#$%'],
            'X-Bar' => ['abc', 'def'],
        ];

        $requestBody = new RequestBody(new InMemoryStream('test'));

        $requestStringifier = new RequestStringifier($method, $path, $headers, $requestBody, null);
        $stringified = yield $requestStringifier->stringify();

        static::assertStringContainsString('/foo/bar', $stringified);
        static::assertStringContainsString('GET', $stringified);
        static::assertStringContainsString('X-Foo', $stringified);
        static::assertStringContainsString('X-Bar', $stringified);
        static::assertStringContainsString('!@#', $stringified);
        static::assertStringContainsString('#$%', $stringified);
        static::assertStringContainsString('abc', $stringified);
        static::assertStringContainsString('def', $stringified);
        static::assertStringContainsString('test', $stringified);
    }

    public function testLongRequestBody(): \Generator
    {
        $longString = \str_repeat('test', StringifierInterface::MAX_BODY_LENGTH);
        $requestBody = new RequestBody(new InMemoryStream($longString));
        $requestStringifier = new RequestStringifier('', '', [], $requestBody);

        $stringified = yield $requestStringifier->stringify();

        static::assertMatchesRegularExpression('/Body: .*?\.\.\./', $stringified);
    }
}
