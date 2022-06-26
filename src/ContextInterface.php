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
     * Returns request body.
     *
     */
    public function requestBody(): RequestBody;

    /**
     * Returns buffered request body.
     *
     * @return Promise<string>
     */
    public function bufferedBody(): Promise;

    public function validatedBody(): Promise;

    /**
     * @see \Amp\Http\Server\FormParser\StreamingParser::parseForm
     */
    public function streamedForm(): Iterator;

    /**
     * @psalm-return Promise<Form>
     * @see \Amp\Http\Server\FormParser\BufferingParser::parseForm
     */
    public function bufferedForm(): Promise;

    /**
     * @return Promise<ValidationContext>
     */
    public function validatedForm(): Promise;

    /**
     * Gets request query as string.
     */
    public function queryString(): string;

    /**
     * Gets request query as array.
     */
    public function queryArray(): array;

    /**
     * Gets session associated with this request.
     */
    public function session(): Session;

    /**
     * Gets a request attribute.
     */
    public function attr(string $name): mixed;

    /**
     * Checks for a request attribute.
     */
    public function hasAttr(string $name): bool;

    /**
     * Gets a request cookie.
     */
    public function cookie(string $name): ?RequestCookie;

    /**
     * Gets all request cookies.
     *
     * @return RequestCookie[]
     */
    public function cookies(): array;

    /**
     * Sets a response cookie.
     *
     * @throws InvalidCookieException
     */
    public function responseCookie(string $name, string $value = '', ?CookieAttributes $attributes = null): self;

    /**
     * Removes a response cookie.
     */
    public function removeCookie(string $name): self;

    /**
     * Gets request port.
     */
    public function port(): ?int;

    /**
     * Gets request host.
     *
     */
    public function host(): string;

    /**
     * Gets request method.
     *
     */
    public function method(): string;

    /**
     * Gets the user agent string.
     */
    public function userAgent(): ?string;

    /**
     * Sets up a redirect.
     */
    public function redirect(string $uri, int $status = Status::FOUND): self;

    /**
     * Sets response status.
     */
    public function status(int $status, ?string $reason = null): self;

    /**
     * Sets response content type.
     */
    public function contentType(string $contentType): self;

    /**
     * Sets a response header.
     */
    public function header(string $name, mixed $value): self;

    /**
     * Removes a response header.
     */
    public function removeHeader(string $name): self;

    /**
     * Gets a request header.
     */
    public function requestHeader(string $name): ?string;

    /**
     * Sends a string or stream response.
     */
    public function response(InputStream|string $stringOrStream): self;

    /**
     * Gets response body to be sent.
     */
    public function responseBody(): InputStream|string;

    /**
     * Sends an HTML response.
     */
    public function html(InputStream|string $stringOrStream): self;

    /**
     * Sends a JSON response.
     *
     * @throws \JsonException
     */
    public function json(mixed $data): self;

    /**
     * Halts the execution of the current request handlers.
     * It throws an instance of @see HaltException.
     * This exception is not meant to be caught.
     */
    public function halt(
        int $status = Status::OK,
        InputStream|string|null $stringOrStream = null,
        array $headers = []
    ): void;

    /**
     * Gets wildcard params from path.
     */
    public function wildcards(): array;

    /**
     * Gets the underlying request object.
     */
    public function getHttpServerRequest(): Request;

    /**
     * Gets the underlying response object.
     */
    public function getHttpServerResponse(): Response;
}
