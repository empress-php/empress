<?php

namespace Empress\Services;

use Amp\Http\Status;
use Amp\Http\Server\Response;
use Pimple\Container;

class ResponseService extends AbstractService
{
    public function getServiceObject()
    {
        return $this;
    }

    public function with($payload, $headers = [], $status = Status::OK)
    {
        $headers = array_merge([
            'content-type' => 'text/plain; charset=utf-8'
        ], $headers);

        return new Response($status, $headers, $payload);
    }
}
