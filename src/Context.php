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
use Amp\Http\Server\MissingAttributeError;
use Amp\Http\Server\Request;
use Amp\Http\Server\Response;
use Amp\Http\Server\Session\Session;
use Amp\Http\Status;
use Amp\Iterator;
use Amp\Promise;
use ArrayAccess;
use Empress\Exception\HaltException;
use Empress\Routing\Router;
use Empress\Util\Optional;
use JsonException;
use LogicException;
use Throwable;
use function Amp\Http\Server\redirectTo;

class Context implements ArrayAccess
{

    /**
     * @var Request
     */
    private $req;

    /**
     * @var Response
     */
    private $res;

    /**
     * @var Promise<Form>
     */
    private $parsedRequestBody;

    /**
     * @var BufferingParser
     */
    private $bufferingParser;

    /**
     * @var StreamingParser
     */
    private $streamingParser;

    /**
     * @var string
     */
    private $queryString;

    /**
     * @var array
     */
    private $queryArray;

    /**
     * @var array
     */
    private $params;

    /**
     * @var Session|null
     */
    private $session;

    /**
     * @var Throwable
     */
    private $exception;

    /**
     * @var string|InputStream
     */
    private $stringOrStream;

    /**
     * Context constructor.
     *
     * @param Request $req
     * @param Response $res
     * @param Throwable|null $exception
     */
    public function __construct(Request $req, Response $res, Throwable $exception = null)
    {
        $this->req = $req;
        $this->res = $res;
        $this->exception = $exception;
        $this->stringOrStream = null;

        $this->bufferingParser = new BufferingParser();
        $this->streamingParser = new StreamingParser();

        $this->queryString = $req->getUri()->getQuery();
        \parse_str($this->queryString, $parsed);
        $this->queryArray = $parsed;

//        $this->params = $this->getRequestAttribute(Router::class, []);
//        $this->session = $this->getRequestAttribute(Session::class, null);

        $this->params = $this->req->getAttribute(Router::class);
        $this->session = $this->req->getAttribute(Session::class);
        $this->exception = $exception;
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
    public function offsetGet($offset)
    {
        return $this->params[$offset];
    }

    /**
     * @inheritDoc
     */
    public function offsetSet($offset, $value)
    {
        throw new LogicException('Cannot set values of an existing request object');
    }

    /**
     * @inheritDoc
     */
    public function offsetUnset($offset)
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
        return $this->req->getBody()->read();
    }

    /**
     * Returns buffered request body.
     *
     * @return Promise<string>
     */
    public function bufferedBody(): Promise
    {
        return $this->req->getBody()->buffer();
    }

    /**
     * @return Iterator
     * @see \Amp\Http\Server\FormParser\StreamingParser::parseForm
     */
    public function streamedForm(): Iterator
    {
        return $this->streamingParser->parseForm($this->req);
    }

    /**
     * @return Promise<Form>
     * @see \Amp\Http\Server\FormParser\BufferingParser::parseForm
     */
    public function bufferedForm(): Promise
    {
        return $this->bufferingParser->parseForm($this->req);
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
    public function attr(string $name)
    {
        return $this->req->getAttribute($name);
    }

    /**
     * Checks for a request attribute.
     *
     * @param string $name
     * @return bool
     */
    public function hasAttr(string $name): bool
    {
        return $this->req->hasAttribute($name);
    }

    /**
     * Gets a request cookie.
     *
     * @param string $name
     * @return RequestCookie
     */
    public function cookie(string $name): RequestCookie
    {
        return $this->req->getCookie($name);
    }

    /**
     * Gets all request cookies.
     *
     * @return RequestCookie[]
     */
    public function cookies(): array
    {
        return $this->req->getCookies();
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
        $this->res->setCookie($cookie);

        return $this;
    }

    public function removeCookie(string $name)
    {
        $this->res->removeCookie($name);

        return $this;
    }

    /**
     * Gets client port.
     *
     * @return int|null
     */
    public function port(): int
    {
        return $this->req->getClient()->getLocalPort();
    }

    /**
     * Gets client host.
     *
     * @return string
     */
    public function host(): string
    {
        return $this->req->getClient()->getLocalAddress();
    }

    /**
     * Gets request method.
     *
     * @return string
     */
    public function method(): string
    {
        return $this->req->getMethod();
    }

    /**
     * Gets the user agent string.
     *
     * @return string|null
     */
    public function userAgent(): string
    {
        return $this->req->getHeader('User-Agent');
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
        $this->res = redirectTo($uri, $status);

        return $this;
    }

    /**
     * Sets response status.
     *
     * @param int $status
     * @param string|null $reason
     * @return $this
     */
    public function status(int $status, string $reason = null): self
    {
        $this->res->setStatus($status, $reason);

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
        $this->res->setHeader('Content-Type', $contentType);

        return $this;
    }

    public function header(string $name, $value): self
    {
        $this->res->setHeader($name, $value);

        return $this;
    }

    public function removeHeader(string $name): self
    {
        $this->res->removeHeader($name);

        return $this;
    }

    public function requestHeader(string $name): ?string
    {
        return $this->req->getHeader($name);
    }

    /**
     * Sends a string or stream response.
     *
     * @param $stringOrStream
     * @return $this
     */
    public function respond($stringOrStream): self
    {
        $this->stringOrStream = $stringOrStream;

        $this->res->setBody($stringOrStream);

        return $this;
    }

    /**
     * Gets response body to be sent
     *
     * @return string|InputStream
     */
    public function responseBody()
    {
        return $this->stringOrStream;
    }

    /**
     * Sends a HTML response.
     *
     * @param $stringOrStream
     * @return $this
     */
    public function html($stringOrStream): self
    {
        $this
            ->contentType('text/html')
            ->respond($stringOrStream);

        return $this;
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

        if (\PHP_VERSION >= 70300) {
            $result = \json_encode($data, JSON_THROW_ON_ERROR);
        } else {
            $result = \json_encode($data);

            if (($lastError = \json_last_error()) !== JSON_ERROR_NONE) {
                throw new JsonException(\json_last_error_msg(), $lastError);
            }
        }

        $this->respond($result);

        return $this;
    }

    public function halt(int $status = Status::OK, $stringOrStream = null, array $headers = [])
    {
        throw new HaltException($status, $headers, $stringOrStream);
    }

    /**
     * If used inside an exception mapper it returns the exception caught.
     * It returns null otherwise.
     *
     * @return Throwable|null
     */
    public function exception(): ?Throwable
    {
        return $this->exception;
    }

    /**
     * If used inside an exception mapper it re-throws the caught exception.
     * It's a noop if no exception was thrown.
     *
     * @throws Throwable
     */
    public function rethrow(): void
    {
        if ($this->exception !== null) {
            throw $this->exception;
        }
    }

    /**
     * Gets the underlying request object.
     *
     * @return Request
     */
    public function getHttpServerRequest()
    {
        return $this->req;
    }

    /**
     * Gets the underlying response object.
     *
     * @return Response
     */
    public function getHttpServerResponse()
    {
        return $this->res;
    }

//    /**
//     * @param string $attributeName
//     * @param $default
//     * @return mixed
//     */
//    private function getRequestAttribute(string $attributeName, $default)
//    {
//        try {
//            return $this->req->getAttribute($attributeName);
//        } catch (MissingAttributeError $e) {
//            throw new RequestException(sprintf('Incomplete request data. Attribute %s missing', $attributeName));
//        }
//    }
}
