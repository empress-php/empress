<?php

declare(strict_types=1);

namespace Empress;

use Amp\Http\Server\ErrorHandler;
use Amp\Http\Server\Request;
use Amp\Http\Server\Response;
use Amp\Promise;
use Amp\Success;

final class DefaultErrorHandler implements ErrorHandler
{
    public function handleError(int $statusCode, ?string $reason = null, ?Request $request = null): Promise
    {
        static $errorHtml;

        if ($errorHtml === null) {
            $errorHtml = \file_get_contents(__DIR__ . '/../static/error.html');
        }

        $responseBody = \str_replace(
            ['{{error_code}}', '{{error_trace}}'],
            [$statusCode, $reason === null ? '' : \sprintf('<pre>%s</pre>', $reason)],
            $errorHtml
        );

        $response = new Response($statusCode, [
            "content-type" => "text/html; charset=utf-8",
        ], $responseBody);

        $response->setStatus($statusCode, $reason);

        return new Success($response);
    }
}
