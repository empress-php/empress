<?php

declare(strict_types=1);

namespace Empress\Test\Logging;

use Amp\ByteStream\InMemoryStream;
use Amp\Http\Server\RequestBody;
use Amp\PHPUnit\AsyncTestCase;
use Empress\Logging\RequestStringifier;
use Empress\Logging\StringifierInterface;

final class RequestStringifierTest extends AsyncTestCase
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

        self::assertStringContainsString('/foo/bar', $stringified);
        self::assertStringContainsString('GET', $stringified);
        self::assertStringContainsString('X-Foo', $stringified);
        self::assertStringContainsString('X-Bar', $stringified);
        self::assertStringContainsString('!@#', $stringified);
        self::assertStringContainsString('#$%', $stringified);
        self::assertStringContainsString('abc', $stringified);
        self::assertStringContainsString('def', $stringified);
        self::assertStringContainsString('test', $stringified);
    }

    public function testLongRequestBody(): \Generator
    {
        $longString = \str_repeat('test', StringifierInterface::MAX_BODY_LENGTH);
        $requestBody = new RequestBody(new InMemoryStream($longString));
        $requestStringifier = new RequestStringifier('', '', [], $requestBody);

        $stringified = yield $requestStringifier->stringify();

        self::assertMatchesRegularExpression('/Body: .*?\.\.\./', $stringified);
    }
}
