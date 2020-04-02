<?php

namespace Empress\Test;

use Amp\Http\Cookie\RequestCookie;
use Amp\Http\Cookie\ResponseCookie;
use Amp\Http\Server\FormParser\Form;
use Amp\Http\Server\FormParser\StreamedField;
use Amp\Http\Server\Response;
use Amp\Http\Server\Session\Session;
use Amp\Http\Status;
use Amp\PHPUnit\AsyncTestCase;
use Empress\Context;
use Empress\Exception\HaltException;
use JsonException;
use LogicException;
use const INF;

class ContextTest extends AsyncTestCase
{
    use HelperTrait;


    /**
     * @var Context
     */
    private $ctx;

    public function setUp(): void
    {

        // Set example params
        $request = $this->createMockRequest('GET', '/?a=b&c=d', [
            'param1' => 'value1',
            'param2' => 'value2'
        ]);

        $this->ctx = new Context($request, new Response());

        parent::setUp();
    }

    public function testOffsets(): void
    {
        $this->assertNotNull($this->ctx['param1']);
        $this->assertNotNull($this->ctx['param2']);

        $this->assertEquals('value1', $this->ctx['param1']);
        $this->assertEquals('value2', $this->ctx['param2']);

        $this->assertTrue(isset($this->ctx['param1']));
    }

    public function testSettingOffsets(): void
    {
        $this->expectException(LogicException::class);

        $this->ctx['param3'] = 'value3';
    }

    public function testUnsettingOffsets(): void
    {
        $this->expectException(LogicException::class);

        unset($this->ctx['param1']);
    }

    public function testStreamedBody()
    {
        $body = 'Hello';
        $this->ctx->getHttpServerRequest()->setBody($body);
        $contents = '';

        while ($chunk = yield $this->ctx->streamedBody()) {
            $contents .= $chunk;
        }

        $this->assertEquals($body, $contents);
    }

    public function testBufferedBody()
    {
        $body = 'Hello';
        $this->ctx->getHttpServerRequest()->setBody($body);
        $contents = yield $this->ctx->bufferedBody();

        $this->assertEquals($body, $contents);
    }

    public function testStreamedForm()
    {
        $this->ctx->getHttpServerRequest()->setHeader('Content-Type', 'application/x-www-form-urlencoded');
        $this->ctx->getHttpServerRequest()->setBody('field1=value1');

        $form = $this->ctx->streamedForm();

        /** @var StreamedField $field */
        yield $form->advance();
        $field = $form->getCurrent();
        $value = yield $field->read();

        $this->assertEquals('field1', $field->getName());
        $this->assertEquals('value1', $value);
    }

    public function testBufferedForm()
    {
        $this->ctx->getHttpServerRequest()->setHeader('Content-Type', 'application/x-www-form-urlencoded');
        $this->ctx->getHttpServerRequest()->setBody('field1=value1');

        /** @var Form $form */
        $form = yield $this->ctx->bufferedForm();
        $value = $form->getValue('field1');

        $this->assertEquals('value1', $value);
    }

    public function testQueryString()
    {
        $queryString = $this->ctx->queryString();

        $this->assertEquals('a=b&c=d', $queryString);
    }

    public function testQueryArray()
    {
        $queryArray = $this->ctx->queryArray();

        $this->assertEquals([
            'a' => 'b',
            'c' => 'd'
        ], $queryArray);
    }

    public function testSession()
    {
        $this->assertInstanceOf(Session::class, $this->ctx->session());

        // Always return the same instance
        $this->assertSame($this->ctx->session(), $this->ctx->session());
    }

    public function testAttr()
    {
        $this->ctx->getHttpServerRequest()->setAttribute('attr', 'value');
        $value = $this->ctx->attr('attr');

        $this->assertEquals('value', $value);
    }

    public function testHasAttr()
    {
        $this->ctx->getHttpServerRequest()->setAttribute('attr', 'value');

        $this->assertTrue($this->ctx->hasAttr('attr'));
    }

    public function testCookie()
    {
        $this->ctx->getHttpServerRequest()->setCookie(new RequestCookie('cookie'));

        $this->assertInstanceOf(RequestCookie::class, $this->ctx->cookie('cookie'));
    }

    public function testCookies()
    {
        $cookieNames = ['cookie1', 'cookie2', 'cookie3'];

        foreach ($cookieNames as $cookieName) {
            $this->ctx->getHttpServerRequest()->setCookie(new RequestCookie($cookieName));
        }

        $cookies = $this->ctx->cookies();

        foreach ($cookies as $cookieName => $cookie) {
            $this->assertInstanceOf(RequestCookie::class, $this->ctx->cookie($cookieName));
        }
    }

    public function testResponseCookie()
    {
        $this->ctx->responseCookie('response_cookie', 'value');
        $cookie = $this->ctx->getHttpServerResponse()->getCookie('response_cookie');

        $this->assertInstanceOf(ResponseCookie::class, $cookie);
        $this->assertEquals('value', $cookie->getValue());
    }

    public function testRemoveCookie()
    {
        $this->ctx->responseCookie('response_cookie');
        $this->ctx->removeCookie('response_cookie');
        $cookie = $this->ctx->getHttpServerResponse()->getCookie('response_cookie');

        $this->assertNull($cookie);
    }

    public function testPort()
    {
        $this->assertEquals(1234, $this->ctx->port());
    }

    public function testHost()
    {
        $this->assertEquals('example.com', $this->ctx->host());
    }

    public function testMethod()
    {
        $this->assertEquals('GET', $this->ctx->method());
    }

    public function testUserAgent()
    {
        $this->ctx->getHttpServerRequest()->setHeader('User-Agent', 'Empress Client');

        $this->assertEquals('Empress Client', $this->ctx->userAgent());
    }

    public function testRedirect()
    {
        $this->ctx->redirect('/redirect', Status::MOVED_PERMANENTLY);
        $res = $this->ctx->getHttpServerResponse();

        $this->assertEquals('/redirect', $res->getHeader('location'));
        $this->assertEquals(Status::MOVED_PERMANENTLY, $res->getStatus());
    }

    public function testStatus()
    {
        $this->ctx->status(Status::METHOD_NOT_ALLOWED);

        $this->assertEquals(Status::METHOD_NOT_ALLOWED, $this->ctx->getHttpServerResponse()->getStatus());
    }

    public function testContentType()
    {
        $this->ctx->contentType('application/json');
        $contentType = $this->ctx->getHttpServerResponse()->getHeader('Content-Type');

        $this->assertEquals('application/json', $contentType);
    }

    public function testHeader()
    {
        $this->ctx->header('Server', 'amphp/http-server');

        $this->assertEquals('amphp/http-server', $this->ctx->getHttpServerResponse()->getHeader('Server'));
    }

    public function testRemoveHeader()
    {
        $this->ctx->header('Server', 'amphp/http-server');
        $this->ctx->removeHeader('Server');

        $this->assertNull($this->ctx->getHttpServerResponse()->getHeader('Server'));
    }

    public function testRespond()
    {
        $this->ctx->respond('Hello');
        $body = yield $this->ctx->getHttpServerResponse()->getBody()->read();

        $this->assertEquals('Hello', $body);
    }

    public function testHtml()
    {
        $this->ctx->html('<h1>Empress</h1>');
        $res = $this->ctx->getHttpServerResponse();
        $body = yield $res->getBody()->read();

        $this->assertEquals('<h1>Empress</h1>', $body);
        $this->assertEquals('text/html', $res->getHeader('Content-Type'));
    }

    public function testJson()
    {
        $data = ['status' => 'ok'];
        $encoded = \json_encode($data);

        $this->ctx->json($data);

        $body = yield $this->ctx->getHttpServerResponse()->getBody()->read();
        $contentType = $this->ctx->getHttpServerResponse()->getHeader('Content-Type');

        $this->assertEquals($body, $encoded);
        $this->assertEquals('application/json', $contentType);
    }

    public function testJsonFailure()
    {
        $this->expectException(JsonException::class);

        $this->ctx->json([INF]);
    }

    public function testHalt()
    {
        $this->expectException(HaltException::class);

        $this->ctx->halt();
    }

    public function testHaltWithStatus()
    {
        try {
            $this->ctx->halt(Status::METHOD_NOT_ALLOWED);
        } catch (HaltException $e) {
            $response = $e->toResponse();
            $this->assertEquals(Status::METHOD_NOT_ALLOWED, $response->getStatus());
            $this->assertEmpty(yield $response->getBody()->read());
        }
    }

    public function testHaltWithCustomBody()
    {
        try {
            $this->ctx->halt(Status::OK, 'Go away');
        } catch (HaltException $e) {
            $response = $e->toResponse();
            $this->assertEquals('Go away', yield $response->getBody()->read());
        }
    }


    public function testGetHttpServerResponse()
    {

        // Always return the same instance
        $this->assertSame($this->ctx->getHttpServerResponse(), $this->ctx->getHttpServerResponse());
    }

    public function testGetHttpServerRequest()
    {
        // Always return the same instance
        $this->assertSame($this->ctx->getHttpServerRequest(), $this->ctx->getHttpServerRequest());
    }
}
