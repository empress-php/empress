<?php

namespace Empress\Test;

use Amp\Http\Server\Response;
use Amp\PHPUnit\AsyncTestCase;
use Amp\Success;
use Empress\Transformer\JsonTransformer;
use JsonException;

class JsonTransformerTest extends AsyncTestCase
{
    public function testEncodeInf()
    {
        $this->expectException(JsonException::class);

        $responseTransformer = new JsonTransformer();
        $infValue = new Success(\INF);

        yield $responseTransformer->transform($infValue);
    }

    public function testEncodeArray()
    {
        $responseTransformer = new JsonTransformer();
        $array = new Success(['status' => 'ok']);

        /** @var Response $transformed */
        $transformed = yield $responseTransformer->transform($array);

        $this->assertEquals(\json_encode(['status' => 'ok']), yield $transformed->getBody()->read());
    }
}
