<?php

declare(strict_types=1);

namespace Empress;

use Amp\ByteStream\InputStream;
use Amp\Http\Cookie\CookieAttributes;
use Amp\Http\Cookie\InvalidCookieException;
use Amp\Http\Cookie\RequestCookie;
use Amp\Http\Cookie\ResponseCookie;
use Amp\Http\Server\FormParser\BufferingParser;
use Amp\Http\Server\FormParser\Form;
use Amp\Http\Server\FormParser\StreamingParser;
use Amp\Http\Server\Request;
use Amp\Http\Server\RequestBody;
use Amp\Http\Server\Response;
use Amp\Http\Server\Session\Session;
use Amp\Http\Status;
use Amp\Iterator;
use Amp\Promise;
use ArrayAccess;
use Empress\Routing\HaltException;
use Empress\Routing\Router;
use Empress\Sse\SseClient;
use Empress\Validation\Registry\ValidatorRegistryInterface;
use Empress\Validation\ValidationContext;
use LogicException;
use function Amp\call;
use function Amp\Http\Server\redirectTo;

final class Context implements ArrayAccess, ContextInterface
{
    private BufferingParser $bufferingParser;

    private StreamingParser $streamingParser;

    private string $queryString;

    private array $queryArray;

    private array $params;

    private array $wildcards;

    private Session $session;

    private InputStream|string $stringOrStream;

    public function __construct(
        private Request $request,
        private ValidatorRegistryInterface $validatorRegistry,
        private Response $response = new Response()
    ) {
        $this->bufferingParser = new BufferingParser();
        $this->streamingParser = new StreamingParser();

        $this->queryString = $this->request->getUri()->getQuery();
        \parse_str($this->queryString, $parsed);
        $this->queryArray = $parsed;

        $this->params = $this->request->getAttribute(Router::NAMED_PARAMS_ATTR_NAME);
        $this->wildcards = $this->request->getAttribute(Router::WILDCARDS_ATTR_NAME);
        $this->session = $this->request->getAttribute(Session::class);

        $this->stringOrStream = '';
    }

    /**
     * @inheritDoc
     */
    public function offsetExists($offset): bool
    {
        return isset($this->params[$offset]);
    }

    /**
     * @inheritDoc
     */
    public function offsetGet($offset): ValidationContext
    {
        return $this->validatorRegistry->contextFor($this->params[$offset]);
    }

    /**
     * @inheritDoc
     */
    public function offsetSet($offset, $value): void
    {
        throw new LogicException('Cannot set values of an existing request object');
    }

    /**
     * @inheritDoc
     */
    public function offsetUnset($offset): void
    {
        throw new LogicException('Cannot unset values of an existing request object');
    }

    /**
     * Returns request body.
     *
     */
    public function requestBody(): RequestBody
    {
        return $this->request->getBody();
    }

    /**
     * Returns buffered request body.
     *
     * @return Promise<string>
     */
    public function bufferedBody(): Promise
    {
        return $this->request->getBody()->buffer();
    }

    public function validatedBody(): Promise
    {
        return call(function () {
            $buffered = yield $this->bufferedBody();

            return $this->validatorRegistry->contextFor($buffered);
        });
    }

    /**
     * @see \Amp\Http\Server\FormParser\StreamingParser::parseForm
     */
    public function streamedForm(): Iterator
    {
        return $this->streamingParser->parseForm($this->request);
    }

    /**
     * @psalm-return Promise<Form>
     * @see \Amp\Http\Server\FormParser\BufferingParser::parseForm
     */
    public function bufferedForm(): Promise
    {
        return $this->bufferingParser->parseForm($this->request);
    }

    /**
     * @return Promise<ValidationContext>
     */
    public function validatedForm(): Promise
    {
        return call(function () {
            $form = yield $this->bufferedForm();

            return $this->validatorRegistry->contextFor($form);
        });
    }

    /**
     * Gets request query as string.
     */
    public function queryString(): string
    {
        return $this->queryString;
    }

    /**
     * Gets request query as array.
     */
    public function queryArray(): array
    {
        return $this->queryArray;
    }

    /**
     * Gets session associated with this request.
     */
    public function session(): Session
    {
        return $this->session;
    }

    /**
     * Gets a request attribute.
     */
    public function attr(string $name): mixed
    {
        return $this->request->getAttribute($name);
    }

    /**
     * Checks for a request attribute.
     */
    public function hasAttr(string $name): bool
    {
        return $this->request->hasAttribute($name);
    }

    /**
     * Gets a request cookie.
     */
    public function cookie(string $name): ?RequestCookie
    {
        return $this->request->getCookie($name);
    }

    /**
     * Gets all request cookies.
     *
     * @return RequestCookie[]
     */
    public function cookies(): array
    {
        return $this->request->getCookies();
    }

    /**
     * Sets a response cookie.
     *
     * @throws InvalidCookieException
     */
    public function responseCookie(string $name, string $value = '', ?CookieAttributes $attributes = null): self
    {
        $cookie = new ResponseCookie($name, $value, $attributes);
        $this->response->setCookie($cookie);

        return $this;
    }

    /**
     * Removes a response cookie.
     */
    public function removeCookie(string $name): self
    {
        $this->response->removeCookie($name);

        return $this;
    }

    /**
     * Gets request port.
     */
    public function port(): ?int
    {
        return $this->request->getUri()->getPort();
    }

    /**
     * Gets request host.
     *
     */
    public function host(): string
    {
        return $this->request->getUri()->getHost();
    }

    /**
     * Gets request method.
     *
     */
    public function method(): string
    {
        return $this->request->getMethod();
    }

    /**
     * Gets the user agent string.
     */
    public function userAgent(): ?string
    {
        return $this->request->getHeader('User-Agent');
    }

    /**
     * Sets up a redirect.
     */
    public function redirect(string $uri, int $status = Status::FOUND): self
    {
        $this->response = redirectTo($uri, $status);

        return $this;
    }

    /**
     * Sets response status.
     */
    public function status(int $status, ?string $reason = null): self
    {
        $this->response->setStatus($status, $reason);

        return $this;
    }

    /**
     * Sets response content type.
     */
    public function contentType(string $contentType): self
    {
        $this->response->setHeader('Content-Type', $contentType);

        return $this;
    }

    /**
     * Sets a response header.
     */
    public function header(string $name, mixed $value): self
    {
        $this->response->setHeader($name, $value);

        return $this;
    }

    /**
     * Removes a response header.
     */
    public function removeHeader(string $name): self
    {
        $this->response->removeHeader($name);

        return $this;
    }

    /**
     * Gets a request header.
     */
    public function requestHeader(string $name): ?string
    {
        return $this->request->getHeader($name);
    }

    /**
     * Sends a string or stream response.
     */
    public function response(InputStream|string $stringOrStream): self
    {
        $this->stringOrStream = $stringOrStream;

        $this->response->setBody($stringOrStream);

        return $this;
    }

    /**
     * Gets response body to be sent.
     */
    public function responseBody(): InputStream|string
    {
        return $this->stringOrStream;
    }

    /**
     * Sends an HTML response.
     */
    public function html(InputStream|string $stringOrStream): self
    {
        return $this
            ->contentType('text/html')
            ->response($stringOrStream);
    }

    /**
     * Sends a JSON response.
     *
     * @throws \JsonException
     */
    public function json(mixed $data): self
    {
        $this->contentType('application/json');

        $result = \json_encode($data, \JSON_THROW_ON_ERROR);

        return $this->response($result);
    }

    public function sse(callable $callback): self
    {
        $client = new SseClient();

        call($callback, $client)
            ->onResolve(fn () => $client->close());

        return $this
            ->response($client->stream())
            ->contentType('text/event-stream; charset=utf-8');
    }

    /**
     * Halts the execution of the current request handlers.
     * It throws an instance of @see HaltException.
     * This exception is not meant to be caught.
     */
    public function halt(int $status = Status::OK, InputStream|string|null $stringOrStream = null, array $headers = []): void
    {
        throw new HaltException($status, $headers, $stringOrStream);
    }

    /**
     * Gets wildcard params from path.
     */
    public function wildcards(): array
    {
        return $this->wildcards;
    }

    public function getHttpServerRequest(): Request
    {
        return $this->request;
    }

    public function getHttpServerResponse(): Response
    {
        return $this->response;
    }
}
