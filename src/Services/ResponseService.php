<?php

namespace Empress\Services;

use Amp\Http\Status;
use Amp\Http\Server\Response;

class ResponseService
{

    /** @var array */
    private $headers;

    public function __construct()
    {
        $this->headers = [
            'content-type' => 'text/plain; charset=utf-8'
        ];
    }

    public function with($payload, $headers = [], $status = Status::OK): Response
    {
        return new Response($status, $headers ?: $this->headers, $payload);
    }
}
