<?php

namespace Empress\Test\Routing;

use Amp\Http\Server\Response;
use Amp\Http\Status;
use Amp\PHPUnit\AsyncTestCase;
use Amp\Promise;
use Empress\Context;
use Empress\Exception\RouteException;
use Empress\Routing\Routes;
use Empress\Test\DummyController;
use Empress\Test\HelperTrait;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Container\ContainerInterface;
use function Amp\call;

class RoutesTest extends AsyncTestCase
{
    use HelperTrait;

    /**
     * @var Routes
     */
    private $r;

    /**
     * @var MockObject
     */
    private $container;

    public function setUp(): void
    {
        $this->r = new Routes();

        $this->container = $this->getMockBuilder(ContainerInterface::class)->getMock();
        $this->container->expects($this->any())->method('get')->will($this->returnValue(new DummyController()));

        parent::setUp();
    }

    public function testBefore()
    {
        $this->r->get('/', function () {});

        $flag = '';
        $this->r->before(function () use (&$flag) { $flag .= '1'; });
        $this->r->before(function () use (&$flag) { $flag .= '2'; });
        $this->r->before(function () use (&$flag) { $flag .= '3'; });

        $request = $this->createMockRequest();

        yield $this->doRequest($request);

        $this->assertEquals('123', $flag);
    }

    public function testAfter()
    {
        $this->r->get('/', function () {});

        $flag = '';
        $this->r->after(function () use (&$flag) { $flag .= '1'; });
        $this->r->after(function () use (&$flag) { $flag .= '2'; });
        $this->r->after(function () use (&$flag) { $flag .= '3'; });

        $request = $this->createMockRequest();

        yield $this->doRequest($request);

        $this->assertEquals('123', $flag);
    }

    public function testException()
    {

    }

    public function testStatus()
    {

    }



    /** @dataProvider httpMethodProvider */
    public function testHttpMethods(string $method)
    {
        $flag = false;
        $handler = function () use (&$flag) { $flag = true; };

        switch ($method) {
            case 'GET':
                $this->r->get('/', $handler);
            break;

            case 'POST':
                $this->r->post('/', $handler);
            break;

            case 'PUT':
                $this->r->put('/', $handler);
            break;

            case 'DELETE':
                $this->r->delete('/', $handler);
            break;

            case 'PATCH':
                $this->r->patch('/', $handler);
            break;

            case 'HEAD':
                $this->r->head('/', $handler);
            break;

            case 'OPTIONS':
                $this->r->options('/', $handler);
            break;

        }

        $request = $this->createMockRequest($method);
        ;

        yield $this->doRequest($request);

        $this->assertTrue($flag);
    }

    public function testGroup()
    {
        $flag = false;

        $this->r->group('/prefix', function (Routes $r) use (&$flag) {
            $r->get('/router', function () use (&$flag) { $flag = true; });
        });

        $request = $this->createMockRequest('GET', '/prefix/router');
        ;

        yield $this->doRequest($request);

        $this->assertTrue($flag);
    }

    public function testBeforeBreaksResponseChain()
    {
        $flag = '';

        $this->r->before(function (Context $ctx) {
            $ctx->halt();
        });

        $this->r->get('/', function (Context $ctx) use (&$flag) {
            $flag .= '1';
        });

        $request = $this->createMockRequest();

        yield $this->doRequest($request);

        $this->assertEquals('', $flag);
    }

    public function testBeforeBreaksFilterChain()
    {
        $this->r->before(function (Context $ctx) {
            $ctx->halt();
        });

        $this->r->get('/', function (Context $ctx) use (&$flag) {
            $flag .= '1';
        });

        $this->r->after(function () use (&$flag) {
            $flag .= '2';
        });

        $request = $this->createMockRequest();

        yield $this->doRequest($request);

        $this->assertEquals('', $flag);
    }

    public function testBeforeBreaksInnerFilterChainOnly()
    {
        $flag = '';

        $this->r->group('/group', function (Routes $r) {
            $r->before(function (Context $ctx) {
                $ctx->halt();
            });

            $r->get('/', function (Context $ctx) use (&$flag) {
                $flag .= '1';
            });

            $r->after(function () use (&$flag) {
                $flag .= '2';
            });
        });

        $this->r->after(function () use (&$flag) {
            $flag .= '3';
        });

        $request = $this->createMockRequest('GET', '/group/');

        yield $this->doRequest($request);

        $this->assertEquals('3', $flag);
    }

    public function testContainerHandler()
    {
        $this->container->expects($this->once())->method('has')->will($this->returnValue(true));

        $this->r->useContainer($this->container);
        $this->r->get('/', 'DummyController@dummy');

        $request = $this->createMockRequest();

        /** @var Response $response */
        $response = yield $this->doRequest($request);

        $this->assertEquals(Status::UNAUTHORIZED, $response->getStatus());
    }

    public function testContainerHandlerAbsent()
    {
        $this->expectException(RouteException::class);

        $this->r->useContainer($this->container);
        $this->r->get('/', 'DummyController@dummyzzz');
    }

    public function testContainerHandlerNotCallable()
    {
        $this->expectException(RouteException::class);

        $this->r->useContainer($this->container);
        $this->r->get('/', 'someDummyHandler');
    }

    public function testContainerControllerAbsent()
    {
        $this->expectException(RouteException::class);

        $container = $this->getMockBuilder(ContainerInterface::class)->getMock();
        $container->expects($this->once())->method('has')->will($this->returnValue(false));

        $this->r->useContainer($container);
        $this->r->get('/', 'AnotherDummyController@dummy');
    }

    public function httpMethodProvider(): array
    {
        return [
            ['GET'],
            ['POST'],
            ['PUT'],
            ['DELETE'],
            ['PATCH'],
            ['HEAD'],
            ['OPTIONS'],
        ];
    }

    private function doRequest($request): Promise
    {
        $router = $this->r->getRouter();
        $router->onStart($this->createMockServer());

        return call(function () use ($request, $router) {
            return yield $router->handleRequest($request);
        });
    }
}
