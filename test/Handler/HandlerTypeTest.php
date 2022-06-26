<?php

declare(strict_types=1);

namespace Empress\Test\Handler;

use Empress\Routing\Handler\HandlerType;
use PHPUnit\Framework\TestCase;

final class HandlerTypeTest extends TestCase
{
    public function testFromString(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        self::assertSame(HandlerType::BEFORE, HandlerType::fromString('BEFORE'));
        self::assertSame(HandlerType::AFTER, HandlerType::fromString('AFTER'));
        self::assertSame(HandlerType::GET, HandlerType::fromString('GET'));
        self::assertSame(HandlerType::POST, HandlerType::fromString('POST'));
        self::assertSame(HandlerType::PUT, HandlerType::fromString('PUT'));
        self::assertSame(HandlerType::DELETE, HandlerType::fromString('DELETE'));
        self::assertSame(HandlerType::PATCH, HandlerType::fromString('PATCH'));
        self::assertSame(HandlerType::HEAD, HandlerType::fromString('HEAD'));
        self::assertSame(HandlerType::OPTIONS, HandlerType::fromString('OPTIONS'));

        HandlerType::fromString('xyz');
    }

    public function testToString(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        self::assertSame('BEFORE', HandlerType::toString(HandlerType::BEFORE));
        self::assertSame('AFTER', HandlerType::toString(HandlerType::AFTER));
        self::assertSame('GET', HandlerType::toString(HandlerType::GET));
        self::assertSame('POST', HandlerType::toString(HandlerType::POST));
        self::assertSame('PUT', HandlerType::toString(HandlerType::PUT));
        self::assertSame('DELETE', HandlerType::toString(HandlerType::DELETE));
        self::assertSame('PATCH', HandlerType::toString(HandlerType::PATCH));
        self::assertSame('HEAD', HandlerType::toString(HandlerType::HEAD));
        self::assertSame('OPTIONS', HandlerType::toString(HandlerType::OPTIONS));

        HandlerType::toString(-100);
    }
}
