<?php

declare(strict_types=1);

namespace Empress\Routing;

use Amp\ByteStream\InputStream;
use Amp\Http\Server\Response;
use Amp\Http\Status;
use Error;

final class HaltException extends Error
{
    public function __construct(
        private $status = Status::OK,
        private array $headers = [],
        private InputStream|string|null $stringOrStream = null
    ) {
        parent::__construct();
    }

    public function toResponse(): Response
    {
        return new Response($this->status, $this->headers, $this->stringOrStream);
    }
}
