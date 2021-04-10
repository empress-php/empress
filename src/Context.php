<?php

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
use Amp\Http\Server\Response;
use Amp\Http\Server\Session\Session;
use Amp\Http\Status;
use Amp\Iterator;
use Amp\Promise;
use ArrayAccess;
use Empress\Exception\HaltException;
use Empress\Routing\Router;
use JsonException;
use LogicException;
use function Amp\Http\Server\redirectTo;

class Context implements ArrayAccess
{
    private Request $request;

    private Response $response;

    private BufferingParser $bufferingParser;

    private StreamingParser $streamingParser;

    private string $queryString;

    private array $queryArray;

    private array $params;

    private ?Session $session;

    private InputStream|string $stringOrStream;

    /**
     * Context constructor.
     *
     * @param Request $request
     * @param Response|null $response
     */
    public function __construct(Request $request, Response $response = null)
    {
        $this->request = $request;
        $this->response = $response ?? new Response();

        $this->bufferingParser = new BufferingParser();
        $this->streamingParser = new StreamingParser();

        $this->queryString = $this->request->getUri()->getQuery();
        \parse_str($this->queryString, $parsed);
        $this->queryArray = $parsed;

        $this->params = $this->request->getAttribute(Router::NAMED_PARAMS_ATTR_NAME);
        $this->session = $this->request->getAttribute(Session::class);
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
    public function offsetGet($offset): mixed
    {
        return $this->params[$offset];
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
     * Returns streamed request.
     *
     * @return Promise<string>
     */
    public function streamedBody(): Promise
    {
        return $this->request->getBody()->read();
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

    /**
     * @return Iterator
     * @see \Amp\Http\Server\FormParser\StreamingParser::parseForm
     */
    public function streamedForm(): Iterator
    {
        return $this->streamingParser->parseForm($this->request);
    }

    /**
     * @return Promise<Form>
     * @see \Amp\Http\Server\FormParser\BufferingParser::parseForm
     */
    public function bufferedForm(): Promise
    {
        return $this->bufferingParser->parseForm($this->request);
    }

    /**
     * Gets request query as string.
     *
     * @return string
     */
    public function queryString(): string
    {
        return $this->queryString;
    }

    /**
     * Gets request query as array.
     *
     * @return array
     */
    public function queryArray(): array
    {
        return $this->queryArray;
    }

    /**
     * Gets session associated with this request.
     *
     * @return Session|null
     */
    public function session(): ?Session
    {
        return $this->session;
    }

    /**
     * Gets a request attribute.
     *
     * @param string $name
     * @return mixed
     */
    public function attr(string $name): mixed
    {
        return $this->request->getAttribute($name);
    }

    /**
     * Checks for a request attribute.
     *
     * @param string $name
     * @return bool
     */
    public function hasAttr(string $name): bool
    {
        return $this->request->hasAttribute($name);
    }

    /**
     * Gets a request cookie.
     *
     * @param string $name
     * @return RequestCookie
     */
    public function cookie(string $name): RequestCookie
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
     * @param string $name
     * @param string $value
     * @param CookieAttributes|null $attributes
     * @return $this
     * @throws InvalidCookieException
     */
    public function responseCookie(string $name, string $value = '', CookieAttributes $attributes = null): self
    {
        $cookie = new ResponseCookie($name, $value, $attributes);
        $this->response->setCookie($cookie);

        return $this;
    }

    public function removeCookie(string $name)
    {
        $this->response->removeCookie($name);

        return $this;
    }

    /**
     * Gets client port.
     *
     * @return int|null
     */
    public function port(): int
    {
        return $this->request->getClient()->getLocalPort();
    }

    /**
     * Gets client host.
     *
     * @return string
     */
    public function host(): string
    {
        return $this->request->getClient()->getLocalAddress();
    }

    /**
     * Gets request method.
     *
     * @return string
     */
    public function method(): string
    {
        return $this->request->getMethod();
    }

    /**
     * Gets the user agent string.
     *
     * @return string|null
     */
    public function userAgent(): string
    {
        return $this->request->getHeader('User-Agent');
    }

    /**
     * Sets up a redirect.
     *
     * @param string $uri
     * @param int $status
     * @return $this
     */
    public function redirect(string $uri, int $status = Status::FOUND): self
    {
        $this->response = redirectTo($uri, $status);

        return $this;
    }

    /**
     * Sets response status.
     *
     * @param int $status
     * @param string|null $reason
     * @return $this
     */
    public function status(int $status, ?string $reason = null): self
    {
        $this->response->setStatus($status, $reason);

        return $this;
    }

    /**
     * Sets response content type.
     *
     * @param string $contentType
     * @return $this
     */
    public function contentType(string $contentType): self
    {
        $this->response->setHeader('Content-Type', $contentType);

        return $this;
    }

    public function header(string $name, $value): self
    {
        $this->response->setHeader($name, $value);

        return $this;
    }

    public function removeHeader(string $name): self
    {
        $this->response->removeHeader($name);

        return $this;
    }

    public function requestHeader(string $name): ?string
    {
        return $this->request->getHeader($name);
    }

    /**
     * Sends a string or stream response.
     *
     * @param $stringOrStream
     * @return $this
     */
    public function response($stringOrStream): self
    {
        $this->stringOrStream = $stringOrStream;

        $this->response->setBody($stringOrStream);

        return $this;
    }

    /**
     * Gets response body to be sent.
     *
     * @return string|InputStream
     */
    public function responseBody(): InputStream|string
    {
        return $this->stringOrStream;
    }

    /**
     * Sends an HTML response.
     *
     * @param $stringOrStream
     * @return $this
     */
    public function html($stringOrStream): self
    {
        return $this
            ->contentType('text/html')
            ->response($stringOrStream);
    }

    /**
     * Sends a JSON response.
     *
     * @param array $data
     * @return $this
     * @throws JsonException
     */
    public function json(array $data): self
    {
        $this->contentType('application/json');

        $result = \json_encode($data, JSON_THROW_ON_ERROR);

        return $this->response($result);
    }

    public function halt(int $status = Status::OK, $stringOrStream = null, array $headers = [])
    {
        throw new HaltException($status, $headers, $stringOrStream);
    }

    /**
     * Gets the underlying request object.
     *
     * @return Request
     */
    public function getHttpServerRequest(): Request
    {
        return $this->request;
    }

    /**
     * Gets the underlying response object.
     *
     * @return Response
     */
    public function getHttpServerResponse(): Response
    {
        return $this->response;
    }
}
