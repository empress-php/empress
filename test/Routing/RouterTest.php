<?php

namespace Empress\Test\Routing;

use Amp\Http\Server\RequestHandler;
use Amp\Http\Server\Response;
use Amp\Http\Server\Server;
use Amp\Http\Server\StaticContent\DocumentRoot;
use Amp\Http\Status;
use Amp\PHPUnit\AsyncTestCase;
use Amp\Socket\Server as SocketServer;
use Empress\Context;
use Empress\Routing\Exception\ExceptionHandler;
use Empress\Routing\Exception\ExceptionMapper;
use Empress\Routing\HandlerEntry;
use Empress\Routing\HandlerType;
use Empress\Routing\Path;
use Empress\Routing\PathMatcher;
use Empress\Routing\Router;
use Empress\Routing\Status\StatusHandler;
use Empress\Routing\Status\StatusMapper;
use Empress\Test\Helper\MockRequestTrait;
use Error;
use InvalidArgumentException;
use Psr\Log\LoggerInterface;

class RouterTest extends AsyncTestCase
{
    use MockRequestTrait;

    public function testServerAlreadyRunning()
    {
        $this->expectException(Error::class);

        $exceptionMapper = $this->createMock(ExceptionMapper::class);
        $statusMapper = $this->createMock(StatusMapper::class);
        $matcher = $this->createMock(PathMatcher::class);

        $router = new Router($exceptionMapper, $statusMapper, $matcher);

        yield $router->onStart($this->getMockServer());
        yield $router->onStart($this->getMockServer());
    }

    public function testNoRoutesRegistered()
    {
        $this->expectException(Error::class);

        $exceptionMapper = $this->createMock(ExceptionMapper::class);
        $statusMapper = $this->createMock(StatusMapper::class);

        $matcher = $this->createMock(PathMatcher::class);
        $matcher->method('hasEntries')->willReturn(false);

        $router = new Router($exceptionMapper, $statusMapper, $matcher);
        yield $router->onStart($this->getMockServer());
    }

    public function testHandleRequest()
    {
        $exceptionMapper = new ExceptionMapper();
        $statusMapper = new StatusMapper();

        $matcher = new PathMatcher();
        $matcher->addEntry(new HandlerEntry(HandlerType::GET, new Path('/'), function (Context $ctx) {
            $ctx->html('<h1>Hello World!</h1>');
        }));

        $router = new Router($exceptionMapper, $statusMapper, $matcher);
        yield $router->onStart($this->getMockServer());

        $request = $this->createMockRequest();

        /** @var Response $response */
        $response = yield $router->handleRequest($request);

        static::assertEquals('<h1>Hello World!</h1>', yield $response->getBody()->read());
    }

    public function testHandleNotFound()
    {
        $exceptionMapper = new ExceptionMapper();
        $statusMapper = new StatusMapper();

        $matcher = new PathMatcher();
        $matcher->addEntry(new HandlerEntry(HandlerType::GET, new Path('/'), fn () => null));

        $router = new Router($exceptionMapper, $statusMapper, $matcher);
        yield $router->onStart($this->getMockServer());

        $request = $this->createMockRequest('GET', '/hello');

        /** @var Response $response */
        $response = yield $router->handleRequest($request);

        static::assertEquals(Status::NOT_FOUND, $response->getStatus());
    }

    public function testHandleMethodNotAllowed()
    {
        $exceptionMapper = new ExceptionMapper();
        $statusMapper = new StatusMapper();

        $matcher = new PathMatcher();
        $matcher->addEntry(new HandlerEntry(HandlerType::GET, new Path('/'), fn () => null));

        $router = new Router($exceptionMapper, $statusMapper, $matcher);
        yield $router->onStart($this->getMockServer());

        $request = $this->createMockRequest('POST');

        /** @var Response $response */
        $response = yield $router->handleRequest($request);

        static::assertEquals(Status::METHOD_NOT_ALLOWED, $response->getStatus());
    }

    public function testWithExceptionMapper()
    {
        $exceptionMapper = new ExceptionMapper();
        $exceptionMapper->addHandler(new ExceptionHandler(function (Context $ctx) {
            $ctx->status(Status::BAD_REQUEST);
        }, InvalidArgumentException::class));

        $statusMapper = new StatusMapper();

        $matcher = new PathMatcher();
        $matcher->addEntry(new HandlerEntry(HandlerType::GET, new Path('/'), function () {
            throw new InvalidArgumentException('Inv4lid');
        }));

        $router = new Router($exceptionMapper, $statusMapper, $matcher);
        yield $router->onStart($this->getMockServer());

        $request = $this->createMockRequest();

        /** @var Response $response */
        $response = yield $router->handleRequest($request);

        static::assertEquals(Status::BAD_REQUEST, $response->getStatus());
    }

    public function testWithUncaughtException()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Inv4lid');

        $exceptionMapper = new ExceptionMapper();
        $statusMapper = new StatusMapper();

        $matcher = new PathMatcher();
        $matcher->addEntry(new HandlerEntry(HandlerType::GET, new Path('/'), function () {
            throw new InvalidArgumentException('Inv4lid');
        }));

        $router = new Router($exceptionMapper, $statusMapper, $matcher);
        yield $router->onStart($this->getMockServer());

        $request = $this->createMockRequest();

        yield $router->handleRequest($request);
    }

    public function testWithHalt()
    {
        $exceptionMapper = new ExceptionMapper();
        $statusMapper = new StatusMapper();

        $matcher = new PathMatcher();
        $matcher->addEntry(new HandlerEntry(HandlerType::GET, new Path('/'), function (Context $ctx) {
            $ctx->halt(Status::NOT_FOUND, 'Not found :(');
        }));

        $router = new Router($exceptionMapper, $statusMapper, $matcher);
        yield $router->onStart($this->getMockServer());

        $request = $this->createMockRequest();

        /** @var Response $response */
        $response = yield $router->handleRequest($request);

        static::assertEquals(Status::NOT_FOUND, $response->getStatus());
        static::assertEquals('Not found :(', yield $response->getBody()->read());
    }

    public function testWithStatusMapper()
    {
        $exceptionMapper = new ExceptionMapper();

        $statusMapper = new StatusMapper();
        $statusMapper->addHandler(new StatusHandler(
            fn (Context $ctx) => $ctx->response('Internal server error'),
            Status::INTERNAL_SERVER_ERROR
        ));

        $matcher = new PathMatcher();
        $matcher->addEntry(new HandlerEntry(HandlerType::GET, new Path('/'), function (Context $ctx) {
            $ctx->status(Status::INTERNAL_SERVER_ERROR);
        }));

        $router = new Router($exceptionMapper, $statusMapper, $matcher);
        yield $router->onStart($this->getMockServer());

        $request = $this->createMockRequest();

        /** @var Response $response */
        $response = yield $router->handleRequest($request);

        static::assertEquals(Status::INTERNAL_SERVER_ERROR, $response->getStatus());
        static::assertEquals('Internal server error', yield $response->getBody()->read());
    }

    public function testWithBefore()
    {
        $exceptionMapper = new ExceptionMapper();
        $statusMapper = new StatusMapper();

        $matcher = new PathMatcher();
        $matcher->addEntry(new HandlerEntry(HandlerType::BEFORE, new Path('/'), function (Context $ctx) {
            $ctx->status(Status::BAD_REQUEST);
        }));
        $matcher->addEntry(new HandlerEntry(HandlerType::GET, new Path('/'), function (Context $ctx) {
            $ctx->response('Hello');
        }));

        $router = new Router($exceptionMapper, $statusMapper, $matcher);
        yield $router->onStart($this->getMockServer());

        $request = $this->createMockRequest();

        /** @var Response $response */
        $response = yield $router->handleRequest($request);

        static::assertEquals(Status::BAD_REQUEST, $response->getStatus());
        static::assertEquals('Hello', yield $response->getBody()->read());
    }

    public function testWithAfter()
    {
        $exceptionMapper = new ExceptionMapper();
        $statusMapper = new StatusMapper();

        $matcher = new PathMatcher();
        $matcher->addEntry(new HandlerEntry(HandlerType::AFTER, new Path('/'), function (Context $ctx) {
            $ctx->status(Status::BAD_REQUEST);
        }));
        $matcher->addEntry(new HandlerEntry(HandlerType::GET, new Path('/'), function (Context $ctx) {
            $ctx->response('Hello');
        }));

        $router = new Router($exceptionMapper, $statusMapper, $matcher);
        yield $router->onStart($this->getMockServer());

        $request = $this->createMockRequest();

        /** @var Response $response */
        $response = yield $router->handleRequest($request);

        static::assertEquals(Status::BAD_REQUEST, $response->getStatus());
        static::assertEquals('Hello', yield $response->getBody()->read());
    }


    public function testCannotSetFallbackWhileRunning()
    {
        $this->expectException(Error::class);

        $exceptionMapper = $this->createMock(ExceptionMapper::class);
        $statusMapper = $this->createMock(StatusMapper::class);

        $matcher = $this->createMock(PathMatcher::class);
        $matcher->method('hasEntries')->willReturn(true);

        $router = new Router($exceptionMapper, $statusMapper, $matcher);
        yield $router->onStart($this->getMockServer());

        $router->setFallback(new DocumentRoot('/'));
    }

    private function getMockServer(): Server
    {
        $socketServer = $this->createMock(SocketServer::class);

        return new Server(
            [$socketServer],
            $this->createMock(RequestHandler::class),
            $this->createMock(LoggerInterface::class)
        );
    }
}
