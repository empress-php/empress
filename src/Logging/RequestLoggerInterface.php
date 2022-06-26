<?php

declare(strict_types=1);

namespace Empress\Logging;

use Amp\Http\Server\Request;
use Amp\Http\Server\Response;
use Amp\Promise;
use Empress\Routing\Handler\HandlerCollection;

interface RequestLoggerInterface
{
    public function debug(Request $request, Response $response, ?HandlerCollection $handlerCollection = null): Promise;
}
