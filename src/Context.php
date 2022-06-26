<?php

declare(strict_types=1);

namespace Empress;

use Amp\ByteStream\InputStream;
use Amp\Http\Cookie\CookieAttributes;
use Amp\Http\Cookie\RequestCookie;
use Amp\Http\Cookie\ResponseCookie;
use Amp\Http\Server\FormParser\BufferingParser;
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
use Empress\Validation\Registry\ValidatorRegistryInterface;
use Empress\Validation\ValidationContext;
use LogicException;
use function Amp\call;
use function Amp\Http\Server\redirectTo;

final class Context implements ArrayAccess, ContextInterface
{
    private Request $request;

    private Response $response;

    private BufferingParser $bufferingParser;

    private StreamingParser $streamingParser;

    private string $queryString;

    private array $queryArray;

    private array $params;

    private array $wildcards;

    private Session $session;

    private InputStream|string $stringOrStream;

    private ValidatorRegistryInterface $validatorRegistry;

    public function __construct(Request $request, ValidatorRegistryInterface $validatorRegistry, ?Response $response = null)
    {
        $this->request = $request;
        $this->response = $response ?? new Response();

        $this->bufferingParser = new BufferingParser();
        $this->streamingParser = new StreamingParser();

        $this->queryString = $this->request->getUri()->getQuery();
        \parse_str($this->queryString, $parsed);
        $this->queryArray = $parsed;

        $this->params = $this->request->getAttribute(Router::NAMED_PARAMS_ATTR_NAME);
        $this->wildcards = $this->request->getAttribute(Router::WILDCARDS_ATTR_NAME);
        $this->session = $this->request->getAttribute(Session::class);

        $this->stringOrStream = '';

        $this->validatorRegistry = $validatorRegistry;
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

    public function requestBody(): RequestBody
    {
        return $this->request->getBody();
    }

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

    public function streamedForm(): Iterator
    {
        return $this->streamingParser->parseForm($this->request);
    }

    public function bufferedForm(): Promise
    {
        return $this->bufferingParser->parseForm($this->request);
    }

    public function validatedForm(): Promise
    {
        return call(function () {
            $form = yield $this->bufferedForm();

            return $this->validatorRegistry->contextFor($form);
        });
    }

    public function queryString(): string
    {
        return $this->queryString;
    }

    public function queryArray(): array
    {
        return $this->queryArray;
    }

    public function session(): Session
    {
        return $this->session;
    }

    public function attr(string $name): mixed
    {
        return $this->request->getAttribute($name);
    }

    public function hasAttr(string $name): bool
    {
        return $this->request->hasAttribute($name);
    }

    public function cookie(string $name): ?RequestCookie
    {
        return $this->request->getCookie($name);
    }

    public function cookies(): array
    {
        return $this->request->getCookies();
    }

    public function responseCookie(string $name, string $value = '', ?CookieAttributes $attributes = null): ContextInterface
    {
        $cookie = new ResponseCookie($name, $value, $attributes);
        $this->response->setCookie($cookie);

        return $this;
    }

    public function removeCookie(string $name): ContextInterface
    {
        $this->response->removeCookie($name);

        return $this;
    }

    public function port(): ?int
    {
        return $this->request->getUri()->getPort();
    }

    public function host(): string
    {
        return $this->request->getUri()->getHost();
    }

    public function method(): string
    {
        return $this->request->getMethod();
    }

    public function userAgent(): ?string
    {
        return $this->request->getHeader('User-Agent');
    }

    public function redirect(string $uri, int $status = Status::FOUND): ContextInterface
    {
        $this->response = redirectTo($uri, $status);

        return $this;
    }

    public function status(int $status, ?string $reason = null): ContextInterface
    {
        $this->response->setStatus($status, $reason);

        return $this;
    }

    public function contentType(string $contentType): ContextInterface
    {
        $this->response->setHeader('Content-Type', $contentType);

        return $this;
    }

    public function header(string $name, mixed $value): ContextInterface
    {
        $this->response->setHeader($name, $value);

        return $this;
    }

    public function removeHeader(string $name): ContextInterface
    {
        $this->response->removeHeader($name);

        return $this;
    }

    public function requestHeader(string $name): ?string
    {
        return $this->request->getHeader($name);
    }

    public function response(InputStream|string $stringOrStream): ContextInterface
    {
        $this->stringOrStream = $stringOrStream;

        $this->response->setBody($stringOrStream);

        return $this;
    }

    public function responseBody(): InputStream|string
    {
        return $this->stringOrStream;
    }

    public function html(InputStream|string $stringOrStream): ContextInterface
    {
        return $this
            ->contentType('text/html')
            ->response($stringOrStream);
    }

    public function json(mixed $data): ContextInterface
    {
        $this->contentType('application/json');

        $result = \json_encode($data, \JSON_THROW_ON_ERROR);

        return $this->response($result);
    }

    public function halt(int $status = Status::OK, InputStream|string|null $stringOrStream = null, array $headers = []): void
    {
        throw new HaltException($status, $headers, $stringOrStream);
    }

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
