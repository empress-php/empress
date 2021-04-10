<?php

namespace Empress\Exception;

use Amp\ByteStream\InputStream;
use Amp\Http\Server\Response;
use Amp\Http\Status;
use Error;

class HaltException extends Error
{
    private int $status;

    private array $headers;

    private InputStream|string|null $stringOrStream;

    /**
     * HaltException constructor.
     * @param int $status
     * @param array $headers
     * @param mixed|null $stringOrStream
     */
    public function __construct($status = Status::OK, array $headers = [], InputStream|string|null $stringOrStream = null)
    {
        $this->status = $status;
        $this->headers = $headers;
        $this->stringOrStream = $stringOrStream;

        parent::__construct();
    }

    public function toResponse(): Response
    {
        return new Response($this->status, $this->headers, $this->stringOrStream);
    }
}
