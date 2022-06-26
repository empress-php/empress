<?php

declare(strict_types=1);

namespace Empress\Test\Handler;

use Empress\Routing\Handler\HandlerEntry;
use Empress\Routing\Handler\HandlerTypeEnum;
use Empress\Routing\Path\Path;
use PHPUnit\Framework\TestCase;

final class HandlerEntryTest extends TestCase
{
    public function testSetPath(): void
    {
        $path = new Path('/');
        $entry = new HandlerEntry(
            HandlerTypeEnum::GET,
            $path,
            fn () => null
        );

        self::assertSame($path, $entry->getPath());

        $path = new Path('/hello');
        $entry->setPath($path);

        self::assertSame($path, $entry->getPath());
    }
}
