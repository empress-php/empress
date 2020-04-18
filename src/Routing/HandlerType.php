<?php

namespace Empress\Routing;

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
        switch (strtoupper($str)) {
            case 'BEFORE': return self::BEFORE;
            case 'AFTER': return self::AFTER;
            case 'GET': return self::GET;
            case 'POST': return self::POST;
            case 'PUT': return self::PUT;
            case 'DELETE': return self::DELETE;
            case 'PATCH': return self::PATCH;
            case 'HEAD': return self::HEAD;
            case 'OPTIONS': return self::OPTIONS;
            default:
                throw new InvalidArgumentException(sprintf('Unknown handler type: %s.', $str));
        }
    }

    public static function toString(int $type): string
    {
        switch ($type) {
            case self::GET: return 'GET';
            case self::POST: return 'POST';
            case self::PUT: return 'PUT';
            case self::DELETE: return 'DELETE';
            case self::PATCH: return 'PATCH';
            case self::HEAD: return 'HEAD';
            case self::OPTIONS: return 'OPTIONS';
            case self::BEFORE: return 'BEFORE';
            case self::AFTER: return 'AFTER';
            default:
                throw new InvalidArgumentException('Unknown handler type.');
        }
    }

    public static function isHttpMethod(int $type): bool
    {
        switch ($type) {
            case self::GET:
            case self::POST:
            case self::PUT:
            case self::DELETE:
            case self::PATCH:
            case self::HEAD:
            case self::OPTIONS:
                return true;
            case self::BEFORE:
            case self::AFTER:
                return false;
            default:
                throw new InvalidArgumentException('Unknown handler type.');
        }
    }

    public static function isFilter(int $type): bool
    {
        return !self::isHttpMethod($type);
    }
}
