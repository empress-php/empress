<?php

namespace Empress\Test\Routing;

use Amp\PHPUnit\AsyncTestCase;
use Amp\Promise;
use Empress\Routing\RouteConfigurator;
use Empress\Test\HelperTrait;
use function Amp\call;

class RouteConfiguratorTest extends AsyncTestCase
{
    use HelperTrait;

    /**
     * @var RouteConfigurator
     */
    private $r;

    public function setUp(): void
    {
        $this->r = new RouteConfigurator();

        parent::setUp();
    }

    public function testBefore()
    {
        $this->r->get('/', function () {});

        $flag = '';
        $this->r->before(function () use (&$flag) { $flag .= '1'; });
        $this->r->before(function () use (&$flag) { $flag .= '2'; });
        $this->r->before(function () use (&$flag) { $flag .= '3'; });

        $request = $this->createMockRequest('GET', '/');

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

        $request = $this->createMockRequest('GET', '/');

        yield $this->doRequest($request);

        $this->assertEquals('321', $flag);
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

        $request = $this->createMockRequest($method, '/');
        ;

        yield $this->doRequest($request);

        $this->assertTrue($flag);
    }

    public function testGroup()
    {
        $flag = false;

        $this->r->group('/prefix', function (RouteConfigurator $r) use (&$flag) {
            $r->get('/router', function () use (&$flag) { $flag = true; });
        });

        $request = $this->createMockRequest('GET', '/prefix/router');
        ;

        yield $this->doRequest($request);

        $this->assertTrue($flag);
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
            yield $router->handleRequest($request);
        });
    }
}
