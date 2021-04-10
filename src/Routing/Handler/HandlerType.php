<?php

namespace Empress\Routing\Handler;

use InvalidArgumentException;

abstract class HandlerType
{
    public const BEFORE = 0;
    public const AFTER = 1;
    public const GET = 2;
    public const POST = 3;
    public const PUT = 4;
    public const DELETE = 5;
    public const PATCH = 6;
    public const HEAD = 7;
    public const OPTIONS = 8;

    public static function fromString(string $str): int
    {
        return match (\strtoupper($str)) {
            'BEFORE' => self::BEFORE,
            'AFTER' => self::AFTER,
            'GET' => self::GET,
            'POST' => self::POST,
            'PUT' => self::PUT,
            'DELETE' => self::DELETE,
            'PATCH' => self::PATCH,
            'HEAD' => self::HEAD,
            'OPTIONS' => self::OPTIONS,
            default => throw new InvalidArgumentException(\sprintf('Unknown handler type: %s.', $str)),
        };
    }

    public static function toString(int $type): string
    {
        return match ($type) {
            self::GET => 'GET',
            self::POST => 'POST',
            self::PUT => 'PUT',
            self::DELETE => 'DELETE',
            self::PATCH => 'PATCH',
            self::HEAD => 'HEAD',
            self::OPTIONS => 'OPTIONS',
            self::BEFORE => 'BEFORE',
            self::AFTER => 'AFTER',
            default => throw new InvalidArgumentException('Unknown handler type: %d.', $type),
        };
    }
}
