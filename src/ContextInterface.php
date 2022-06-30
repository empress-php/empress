<?php

declare(strict_types=1);

namespace Empress;

use Amp\Http\Server\Request;
use Amp\Http\Server\Response;

interface ContextInterface
{
    /**
     * Gets the underlying request object.
     */
    public function getHttpServerRequest(): Request;

    /**
     * Gets the underlying response object.
     */
    public function getHttpServerResponse(): Response;
}
