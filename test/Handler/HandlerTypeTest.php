<?php

namespace Empress\Test\Handler;

use Empress\Routing\Handler\HandlerType;
use PHPUnit\Framework\TestCase;

class HandlerTypeTest extends TestCase
{
    public function testFromString(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        static::assertEquals(HandlerType::BEFORE, HandlerType::fromString('BEFORE'));
        static::assertEquals(HandlerType::AFTER, HandlerType::fromString('AFTER'));
        static::assertEquals(HandlerType::GET, HandlerType::fromString('GET'));
        static::assertEquals(HandlerType::POST, HandlerType::fromString('POST'));
        static::assertEquals(HandlerType::PUT, HandlerType::fromString('PUT'));
        static::assertEquals(HandlerType::DELETE, HandlerType::fromString('DELETE'));
        static::assertEquals(HandlerType::PATCH, HandlerType::fromString('PATCH'));
        static::assertEquals(HandlerType::HEAD, HandlerType::fromString('HEAD'));
        static::assertEquals(HandlerType::OPTIONS, HandlerType::fromString('OPTIONS'));

        HandlerType::fromString('xyz');
    }

    public function testToString(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        static::assertEquals('BEFORE', HandlerType::toString(HandlerType::BEFORE));
        static::assertEquals('AFTER', HandlerType::toString(HandlerType::AFTER));
        static::assertEquals('GET', HandlerType::toString(HandlerType::GET));
        static::assertEquals('POST', HandlerType::toString(HandlerType::POST));
        static::assertEquals('PUT', HandlerType::toString(HandlerType::PUT));
        static::assertEquals('DELETE', HandlerType::toString(HandlerType::DELETE));
        static::assertEquals('PATCH', HandlerType::toString(HandlerType::PATCH));
        static::assertEquals('HEAD', HandlerType::toString(HandlerType::HEAD));
        static::assertEquals('OPTIONS', HandlerType::toString(HandlerType::OPTIONS));

        HandlerType::toString(-100);
    }
}
