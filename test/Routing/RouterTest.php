<?php

namespace Empress\Test\Routing;

use Amp\Http\Server\RequestHandler;
use Amp\Http\Server\Response;
use Amp\Http\Server\Server;
use Amp\Http\Server\StaticContent\DocumentRoot;
use Amp\Http\Status;
use Amp\PHPUnit\AsyncTestCase;
use Amp\Socket\Server as SocketServer;
use Amp\Success;
use Empress\Context;
use Empress\Logging\RequestLogger;
use Empress\Routing\Exception\ExceptionHandler;
use Empress\Routing\Exception\ExceptionMapper;
use Empress\Routing\Handler\HandlerCollection;
use Empress\Routing\Handler\HandlerEntry;
use Empress\Routing\Handler\HandlerType;
use Empress\Routing\Path\Path;
use Empress\Routing\Router;
use Empress\Routing\Status\StatusHandler;
use Empress\Routing\Status\StatusMapper;
use Empress\Test\Helper\StubRequestTrait;
use Error;
use InvalidArgumentException;
use Psr\Log\LoggerInterface;

class RouterTest extends AsyncTestCase
{
    use StubRequestTrait;

    public function testServerAlreadyRunning(): \Generator
    {
        $this->expectException(Error::class);

        $exceptionMapper = $this->createMock(ExceptionMapper::class);
        $statusMapper = $this->createMock(StatusMapper::class);
        $collection = $this->createMock(HandlerCollection::class);

        $router = new Router($exceptionMapper, $statusMapper, $collection);

        yield $router->onStart($this->getStubServer());
        yield $router->onStart($this->getStubServer());
    }

    public function testNoRoutesRegistered(): \Generator
    {
        $this->expectException(Error::class);

        $exceptionMapper = $this->createMock(ExceptionMapper::class);
        $statusMapper = $this->createMock(StatusMapper::class);

        $collection = $this->createMock(HandlerCollection::class);
        $collection->method('count')->willReturn(0);

        $router = new Router($exceptionMapper, $statusMapper, $collection);
        yield $router->onStart($this->getStubServer());
    }

    public function testHandleRequest(): \Generator
    {
        $exceptionMapper = new ExceptionMapper();
        $statusMapper = new StatusMapper();

        $collection = new HandlerCollection();
        $collection->add(new HandlerEntry(HandlerType::GET, new Path('/'), function (Context $ctx) {
            $ctx->html('<h1>Hello World!</h1>');
        }));

        $router = new Router($exceptionMapper, $statusMapper, $collection);
        yield $router->onStart($this->getStubServer());

        $request = $this->createStubRequest();

        /** @var Response $response */
        $response = yield $router->handleRequest($request);

        static::assertEquals('<h1>Hello World!</h1>', yield $response->getBody()->read());
    }

    public function testHandleNotFound(): \Generator
    {
        $exceptionMapper = new ExceptionMapper();
        $statusMapper = new StatusMapper();

        $collection = new HandlerCollection();
        $collection->add(new HandlerEntry(HandlerType::GET, new Path('/'), fn () => null));

        $router = new Router($exceptionMapper, $statusMapper, $collection);
        yield $router->onStart($this->getStubServer());

        $request = $this->createStubRequest('GET', '/hello');

        /** @var Response $response */
        $response = yield $router->handleRequest($request);

        static::assertEquals(Status::NOT_FOUND, $response->getStatus());
    }

    public function testHandleMethodNotAllowed(): \Generator
    {
        $exceptionMapper = new ExceptionMapper();
        $statusMapper = new StatusMapper();

        $collection = new HandlerCollection();
        $collection->add(new HandlerEntry(HandlerType::GET, new Path('/'), fn () => null));

        $router = new Router($exceptionMapper, $statusMapper, $collection);
        yield $router->onStart($this->getStubServer());

        $request = $this->createStubRequest('POST');

        /** @var Response $response */
        $response = yield $router->handleRequest($request);

        static::assertEquals(Status::METHOD_NOT_ALLOWED, $response->getStatus());
    }

    public function testWithExceptionMapper(): \Generator
    {
        $exceptionMapper = new ExceptionMapper();
        $exceptionMapper->addHandler(new ExceptionHandler(function (Context $ctx) {
            $ctx->status(Status::BAD_REQUEST);
        }, InvalidArgumentException::class));

        $statusMapper = new StatusMapper();

        $collection = new HandlerCollection();
        $collection->add(new HandlerEntry(HandlerType::GET, new Path('/'), function () {
            throw new InvalidArgumentException('Inv4lid');
        }));

        $router = new Router($exceptionMapper, $statusMapper, $collection);
        yield $router->onStart($this->getStubServer());

        $request = $this->createStubRequest();

        /** @var Response $response */
        $response = yield $router->handleRequest($request);

        static::assertEquals(Status::BAD_REQUEST, $response->getStatus());
    }

    public function testWithUncaughtException(): \Generator
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Inv4lid');

        $exceptionMapper = new ExceptionMapper();
        $statusMapper = new StatusMapper();

        $collection = new HandlerCollection();
        $collection->add(new HandlerEntry(HandlerType::GET, new Path('/'), function () {
            throw new InvalidArgumentException('Inv4lid');
        }));

        $router = new Router($exceptionMapper, $statusMapper, $collection);
        yield $router->onStart($this->getStubServer());

        $request = $this->createStubRequest();

        yield $router->handleRequest($request);
    }

    public function testWithHalt(): \Generator
    {
        $exceptionMapper = new ExceptionMapper();
        $statusMapper = new StatusMapper();

        $collection = new HandlerCollection();
        $collection->add(new HandlerEntry(HandlerType::GET, new Path('/'), function (Context $ctx) {
            $ctx->halt(Status::NOT_FOUND, 'Not found :(');
        }));

        $router = new Router($exceptionMapper, $statusMapper, $collection);
        yield $router->onStart($this->getStubServer());

        $request = $this->createStubRequest();

        /** @var Response $response */
        $response = yield $router->handleRequest($request);

        static::assertEquals(Status::NOT_FOUND, $response->getStatus());
        static::assertEquals('Not found :(', yield $response->getBody()->read());
    }

    public function testWithStatusMapper(): \Generator
    {
        $exceptionMapper = new ExceptionMapper();

        $statusMapper = new StatusMapper();
        $statusMapper->addHandler(new StatusHandler(
            fn (Context $ctx) => $ctx->response('Internal server error'),
            Status::INTERNAL_SERVER_ERROR
        ));

        $collection = new HandlerCollection();
        $collection->add(new HandlerEntry(HandlerType::GET, new Path('/'), function (Context $ctx) {
            $ctx->status(Status::INTERNAL_SERVER_ERROR);
        }));

        $router = new Router($exceptionMapper, $statusMapper, $collection);
        yield $router->onStart($this->getStubServer());

        $request = $this->createStubRequest();

        /** @var Response $response */
        $response = yield $router->handleRequest($request);

        static::assertEquals(Status::INTERNAL_SERVER_ERROR, $response->getStatus());
        static::assertEquals('Internal server error', yield $response->getBody()->read());
    }

    public function testWithBefore(): \Generator
    {
        $exceptionMapper = new ExceptionMapper();
        $statusMapper = new StatusMapper();

        $collection = new HandlerCollection();
        $collection->add(new HandlerEntry(HandlerType::BEFORE, new Path('/'), function (Context $ctx) {
            $ctx->status(Status::BAD_REQUEST);
        }));
        $collection->add(new HandlerEntry(HandlerType::GET, new Path('/'), function (Context $ctx) {
            $ctx->response('Hello');
        }));

        $router = new Router($exceptionMapper, $statusMapper, $collection);
        yield $router->onStart($this->getStubServer());

        $request = $this->createStubRequest();

        /** @var Response $response */
        $response = yield $router->handleRequest($request);

        static::assertEquals(Status::BAD_REQUEST, $response->getStatus());
        static::assertEquals('Hello', yield $response->getBody()->read());
    }

    public function testWithAfter(): \Generator
    {
        $exceptionMapper = new ExceptionMapper();
        $statusMapper = new StatusMapper();

        $collection = new HandlerCollection();
        $collection->add(new HandlerEntry(HandlerType::AFTER, new Path('/'), function (Context $ctx) {
            $ctx->status(Status::BAD_REQUEST);
        }));
        $collection->add(new HandlerEntry(HandlerType::GET, new Path('/'), function (Context $ctx) {
            $ctx->response('Hello');
        }));

        $router = new Router($exceptionMapper, $statusMapper, $collection);
        yield $router->onStart($this->getStubServer());

        $request = $this->createStubRequest();

        /** @var Response $response */
        $response = yield $router->handleRequest($request);

        static::assertEquals(Status::BAD_REQUEST, $response->getStatus());
        static::assertEquals('Hello', yield $response->getBody()->read());
    }


    public function testCannotSetFallbackWhileRunning(): \Generator
    {
        $this->expectException(Error::class);

        $exceptionMapper = new ExceptionMapper();
        $statusMapper = new StatusMapper();

        $collection = $this->createMock(HandlerCollection::class);
        $collection->method('count')->willReturn(1);

        $router = new Router($exceptionMapper, $statusMapper, $collection);
        yield $router->onStart($this->getStubServer());

        $router->setFallback(new DocumentRoot('/'));
    }

    public function testDebugInfoIsLogged(): \Generator
    {
        $exceptionMapper = new ExceptionMapper();
        $statusMapper = new StatusMapper();

        $collection = new HandlerCollection();
        $collection->add(new HandlerEntry(HandlerType::GET, new Path('/'), fn () => null));

        $request = $this->createStubRequest();

        $requestLogger = $this->createMock(RequestLogger::class);
        $requestLogger
            ->expects(static::once())
            ->method('debug')
            ->with($request, static::isInstanceOf(Response::class), $collection)
            ->willReturn(new Success());

        $router = new Router($exceptionMapper, $statusMapper, $collection, $requestLogger);
        yield $router->onStart($this->getStubServer());

        yield $router->handleRequest($request);
    }

    public function testDebugInfoIsLoggedOnError(): \Generator
    {
        $exceptionMapper = new ExceptionMapper();
        $statusMapper = new StatusMapper();

        $collection = new HandlerCollection();
        $collection->add(new HandlerEntry(HandlerType::GET, new Path('/'), fn () => null));

        $request = $this->createStubRequest('POST');

        $requestLogger = $this->createMock(RequestLogger::class);
        $requestLogger
            ->expects(static::once())
            ->method('debug')
            ->with($request, static::isInstanceOf(Response::class))
            ->willReturn(new Success());

        $router = new Router($exceptionMapper, $statusMapper, $collection, $requestLogger);
        yield $router->onStart($this->getStubServer());

        yield $router->handleRequest($request);
    }

    private function getStubServer(): Server
    {
        $socketServer = $this->createMock(SocketServer::class);

        return new Server(
            [$socketServer],
            $this->createMock(RequestHandler::class),
            $this->createMock(LoggerInterface::class)
        );
    }
}
