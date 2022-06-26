<?php

declare(strict_types=1);

namespace Empress\Test\Routing;

use Amp\Http\Server\Response;
use Amp\Http\Server\StaticContent\DocumentRoot;
use Amp\Http\Status;
use Amp\PHPUnit\AsyncTestCase;
use Amp\Success;
use Empress\Context;
use Empress\Logging\RequestLoggerInterface;
use Empress\Routing\Exception\ExceptionHandler;
use Empress\Routing\Exception\ExceptionMapper;
use Empress\Routing\Handler\HandlerCollection;
use Empress\Routing\Handler\HandlerCollectionInterface;
use Empress\Routing\Handler\HandlerEntry;
use Empress\Routing\Handler\HandlerType;
use Empress\Routing\Path\Path;
use Empress\Routing\Router;
use Empress\Routing\Status\StatusHandler;
use Empress\Routing\Status\StatusMapper;
use Empress\Test\Helper\StubRequestTrait;
use Empress\Test\Helper\StubServerTrait;
use Empress\Validation\Registry\ValidatorRegistryInterface;
use Error;
use InvalidArgumentException;

final class RouterTest extends AsyncTestCase
{
    use StubRequestTrait;
    use StubServerTrait;

    private ExceptionMapper $exceptionMapper;

    private StatusMapper $statusMapper;

    private HandlerCollectionInterface $collection;

    private ValidatorRegistryInterface $validatorRegistry;

    protected function setUp(): void
    {
        $this->exceptionMapper = new ExceptionMapper();
        $this->statusMapper = new StatusMapper();
        $this->collection = new HandlerCollection();
        $this->validatorRegistry = $this->createMock(ValidatorRegistryInterface::class);

        parent::setUp();
    }

    public function testServerAlreadyRunning(): \Generator
    {
        $this->expectException(Error::class);

        $router = new Router($this->exceptionMapper, $this->statusMapper, $this->collection, $this->validatorRegistry);

        yield $router->onStart($this->getStubServer());
        yield $router->onStart($this->getStubServer());
    }

    public function testNoRoutesRegistered(): \Generator
    {
        $this->expectException(Error::class);

        $router = new Router($this->exceptionMapper, $this->statusMapper, $this->collection, $this->validatorRegistry);

        yield $router->onStart($this->getStubServer());
    }

    public function testHandleRequest(): \Generator
    {
        $this->collection->add(new HandlerEntry(HandlerType::GET, new Path('/'), function (Context $ctx): void {
            $ctx->html('<h1>Hello World!</h1>');
        }));

        $router = new Router($this->exceptionMapper, $this->statusMapper, $this->collection, $this->validatorRegistry);

        yield $router->onStart($this->getStubServer());

        $request = $this->createStubRequest();

        /** @var Response $response */
        $response = yield $router->handleRequest($request);

        self::assertSame('<h1>Hello World!</h1>', yield $response->getBody()->read());
    }

    public function testHandleNotFound(): \Generator
    {
        $this->collection->add(new HandlerEntry(HandlerType::GET, new Path('/'), fn () => null));

        $router = new Router($this->exceptionMapper, $this->statusMapper, $this->collection, $this->validatorRegistry);

        yield $router->onStart($this->getStubServer());

        $request = $this->createStubRequest('GET', '/hello');

        /** @var Response $response */
        $response = yield $router->handleRequest($request);

        self::assertSame(Status::NOT_FOUND, $response->getStatus());
    }

    public function testHandleMethodNotAllowed(): \Generator
    {
        $this->collection->add(new HandlerEntry(HandlerType::GET, new Path('/'), fn () => null));

        $router = new Router($this->exceptionMapper, $this->statusMapper, $this->collection, $this->validatorRegistry);

        yield $router->onStart($this->getStubServer());

        $request = $this->createStubRequest('POST');

        /** @var Response $response */
        $response = yield $router->handleRequest($request);

        self::assertSame(Status::METHOD_NOT_ALLOWED, $response->getStatus());
    }

    public function testWithExceptionMapper(): \Generator
    {
        $this->exceptionMapper->addHandler(new ExceptionHandler(function (Context $ctx): void {
            $ctx->status(Status::BAD_REQUEST);
        }, InvalidArgumentException::class));

        $this->collection->add(new HandlerEntry(HandlerType::GET, new Path('/'), function (): void {
            throw new InvalidArgumentException('Inv4lid');
        }));

        $router = new Router($this->exceptionMapper, $this->statusMapper, $this->collection, $this->validatorRegistry);

        yield $router->onStart($this->getStubServer());

        $request = $this->createStubRequest();

        /** @var Response $response */
        $response = yield $router->handleRequest($request);

        self::assertSame(Status::BAD_REQUEST, $response->getStatus());
    }

    public function testWithUncaughtException(): \Generator
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Inv4lid');

        $this->collection->add(new HandlerEntry(HandlerType::GET, new Path('/'), function (): void {
            throw new InvalidArgumentException('Inv4lid');
        }));

        $router = new Router($this->exceptionMapper, $this->statusMapper, $this->collection, $this->validatorRegistry);

        yield $router->onStart($this->getStubServer());

        $request = $this->createStubRequest();

        yield $router->handleRequest($request);
    }

    public function testWithHalt(): \Generator
    {
        $this->collection->add(new HandlerEntry(HandlerType::GET, new Path('/'), function (Context $ctx): void {
            $ctx->halt(Status::NOT_FOUND, 'Not found :(');
        }));

        $router = new Router($this->exceptionMapper, $this->statusMapper, $this->collection, $this->validatorRegistry);

        yield $router->onStart($this->getStubServer());

        $request = $this->createStubRequest();

        /** @var Response $response */
        $response = yield $router->handleRequest($request);

        self::assertSame(Status::NOT_FOUND, $response->getStatus());
        self::assertSame('Not found :(', yield $response->getBody()->read());
    }

    public function testWithStatusMapper(): \Generator
    {
        $this->statusMapper->addHandler(new StatusHandler(
            fn (Context $ctx) => $ctx->response('Internal server error'),
            Status::INTERNAL_SERVER_ERROR
        ));

        $this->collection->add(new HandlerEntry(HandlerType::GET, new Path('/'), function (Context $ctx): void {
            $ctx->status(Status::INTERNAL_SERVER_ERROR);
        }));

        $router = new Router($this->exceptionMapper, $this->statusMapper, $this->collection, $this->validatorRegistry);

        yield $router->onStart($this->getStubServer());

        $request = $this->createStubRequest();

        /** @var Response $response */
        $response = yield $router->handleRequest($request);

        self::assertSame(Status::INTERNAL_SERVER_ERROR, $response->getStatus());
        self::assertSame('Internal server error', yield $response->getBody()->read());
    }

    public function testWithBefore(): \Generator
    {
        $this->collection->add(new HandlerEntry(HandlerType::BEFORE, new Path('/'), function (Context $ctx): void {
            $ctx->status(Status::BAD_REQUEST);
        }));

        $this->collection->add(new HandlerEntry(HandlerType::GET, new Path('/'), function (Context $ctx): void {
            $ctx->response('Hello');
        }));

        $router = new Router($this->exceptionMapper, $this->statusMapper, $this->collection, $this->validatorRegistry);

        yield $router->onStart($this->getStubServer());

        $request = $this->createStubRequest();

        /** @var Response $response */
        $response = yield $router->handleRequest($request);

        self::assertSame(Status::BAD_REQUEST, $response->getStatus());
        self::assertSame('Hello', yield $response->getBody()->read());
    }

    public function testWithAfter(): \Generator
    {
        $this->collection->add(new HandlerEntry(HandlerType::AFTER, new Path('/'), function (Context $ctx): void {
            $ctx->status(Status::BAD_REQUEST);
        }));

        $this->collection->add(new HandlerEntry(HandlerType::GET, new Path('/'), function (Context $ctx): void {
            $ctx->response('Hello');
        }));

        $router = new Router($this->exceptionMapper, $this->statusMapper, $this->collection, $this->validatorRegistry);

        yield $router->onStart($this->getStubServer());

        $request = $this->createStubRequest();

        /** @var Response $response */
        $response = yield $router->handleRequest($request);

        self::assertSame(Status::BAD_REQUEST, $response->getStatus());
        self::assertSame('Hello', yield $response->getBody()->read());
    }

    public function testCannotSetFallbackWhileRunning(): \Generator
    {
        $this->expectException(Error::class);

        $this->collection
            ->method('count')
            ->willReturn(1);

        $router = new Router($this->exceptionMapper, $this->statusMapper, $this->collection, $this->validatorRegistry);
        yield $router->onStart($this->getStubServer());

        $router->setFallback(new DocumentRoot('/'));
    }

    public function testDebugInfoIsLogged(): \Generator
    {
        $this->collection->add(new HandlerEntry(HandlerType::GET, new Path('/'), fn () => null));

        $request = $this->createStubRequest();

        $requestLogger = $this->createMock(RequestLoggerInterface::class);
        $requestLogger
            ->expects(self::once())
            ->method('debug')
            ->with($request, self::isInstanceOf(Response::class), $this->collection)
            ->willReturn(new Success());

        $router = new Router($this->exceptionMapper, $this->statusMapper, $this->collection, $this->validatorRegistry, $requestLogger);

        yield $router->onStart($this->getStubServer());

        yield $router->handleRequest($request);
    }

    public function testDebugInfoIsLoggedOnError(): \Generator
    {
        $this->collection->add(new HandlerEntry(HandlerType::GET, new Path('/'), fn () => null));

        $request = $this->createStubRequest('POST');

        $requestLogger = $this->createMock(RequestLoggerInterface::class);
        $requestLogger
            ->expects(self::once())
            ->method('debug')
            ->with($request, self::isInstanceOf(Response::class))
            ->willReturn(new Success());

        $router = new Router($this->exceptionMapper, $this->statusMapper, $this->collection, $this->validatorRegistry, $requestLogger);
        yield $router->onStart($this->getStubServer());

        yield $router->handleRequest($request);
    }
}
