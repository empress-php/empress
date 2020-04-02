<?php

namespace Empress\Exception;

use Amp\Http\Server\Response;
use Amp\Http\Status;
use Error;

class HaltException extends Error
{

    /**
     * @var int
     */
    private $status;

    /**
     * @var array
     */
    private $headers;

    /**
     * @var mixed|null
     */
    private $stringOrStream;

    /**
     * HaltException constructor.
     * @param int $status
     * @param array $headers
     * @param mixed|null $stringOrStream
     */
    public function __construct($status = Status::OK, array $headers = [], $stringOrStream = null)
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
