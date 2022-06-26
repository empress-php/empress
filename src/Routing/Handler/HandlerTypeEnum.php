<?php

declare(strict_types=1);

namespace Empress\Routing\Handler;

enum HandlerTypeEnum: string
{
    case BEFORE = 'BEFORE';
    case AFTER = 'AFTER';
    case GET = 'GET';
    case POST = 'POST';
    case PUT = 'PUT';
    case DELETE = 'DELETE';
    case PATCH = 'PATCH';
    case HEAD = 'HEAD';
    case OPTIONS = 'OPTIONS';
}
