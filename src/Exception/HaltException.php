<?php

namespace Empress\Exception;

use Amp\ByteStream\InputStream;
use Amp\Http\Server\Response;
use Amp\Http\Status;
use Error;

class HaltException extends Error
{

    /**
     * HaltException constructor.
     *
     * @param int $status
     * @param array $headers
     * @param InputStream|string|null $stringOrStream
     */
    public function __construct(
        private $status = Status::OK,
        private array $headers = [],
        private InputStream|string|null $stringOrStream = null)
    {
        parent::__construct();
    }

    public function toResponse(): Response
    {
        return new Response($this->status, $this->headers, $this->stringOrStream);
    }
}
