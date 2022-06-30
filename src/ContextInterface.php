<?php

declare(strict_types=1);

namespace Empress;

use Amp\ByteStream\InputStream;
use Amp\Http\Cookie\CookieAttributes;
use Amp\Http\Cookie\InvalidCookieException;
use Amp\Http\Cookie\RequestCookie;
use Amp\Http\Server\FormParser\Form;
use Amp\Http\Server\Request;
use Amp\Http\Server\RequestBody;
use Amp\Http\Server\Response;
use Amp\Http\Server\Session\Session;
use Amp\Http\Status;
use Amp\Iterator;
use Amp\Promise;
use Empress\Routing\HaltException;
use Empress\Validation\ValidationContext;

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
