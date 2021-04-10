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
use Empress\Test\Helper\StubRequestTrait;
use Generator;
use JsonException;
use LogicException;
use const INF;

class ContextTest extends AsyncTestCase
{
    use StubRequestTrait;

    private Context $ctx;

    public function setUp(): void
    {

        // Set example params
        $request = $this->createStubRequest('GET', '/?a=b&c=d', [
            'param1' => 'value1',
            'param2' => 'value2'
        ]);

        $this->ctx = new Context($request, new Response());

        parent::setUp();
    }

    public function testOffsets(): void
    {
        static::assertNotNull($this->ctx['param1']);
        static::assertNotNull($this->ctx['param2']);

        static::assertEquals('value1', $this->ctx['param1']);
        static::assertEquals('value2', $this->ctx['param2']);

        static::assertTrue(isset($this->ctx['param1']));
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

    public function testStreamedBody(): Generator
    {
        $body = 'Hello';
        $this->ctx->getHttpServerRequest()->setBody($body);
        $contents = '';

        while ($chunk = yield $this->ctx->streamedBody()) {
            $contents .= $chunk;
        }

        static::assertEquals($body, $contents);
    }

    public function testBufferedBody(): Generator
    {
        $body = 'Hello';
        $this->ctx->getHttpServerRequest()->setBody($body);
        $contents = yield $this->ctx->bufferedBody();

        static::assertEquals($body, $contents);
    }

    public function testStreamedForm(): Generator
    {
        $this->ctx->getHttpServerRequest()->setHeader('Content-Type', 'application/x-www-form-urlencoded');
        $this->ctx->getHttpServerRequest()->setBody('field1=value1');

        $form = $this->ctx->streamedForm();

        /** @var StreamedField $field */
        yield $form->advance();
        $field = $form->getCurrent();
        $value = yield $field->read();

        static::assertEquals('field1', $field->getName());
        static::assertEquals('value1', $value);
    }

    public function testBufferedForm(): Generator
    {
        $this->ctx->getHttpServerRequest()->setHeader('Content-Type', 'application/x-www-form-urlencoded');
        $this->ctx->getHttpServerRequest()->setBody('field1=value1');

        /** @var Form $form */
        $form = yield $this->ctx->bufferedForm();
        $value = $form->getValue('field1');

        static::assertEquals('value1', $value);
    }

    public function testQueryString(): void
    {
        $queryString = $this->ctx->queryString();

        static::assertEquals('a=b&c=d', $queryString);
    }

    public function testQueryArray(): void
    {
        $queryArray = $this->ctx->queryArray();

        static::assertEquals([
            'a' => 'b',
            'c' => 'd'
        ], $queryArray);
    }

    public function testSession(): void
    {
        static::assertInstanceOf(Session::class, $this->ctx->session());

        // Always return the same instance
        static::assertSame($this->ctx->session(), $this->ctx->session());
    }

    public function testAttr(): void
    {
        $this->ctx->getHttpServerRequest()->setAttribute('attr', 'value');
        $value = $this->ctx->attr('attr');

        static::assertEquals('value', $value);
    }

    public function testHasAttr(): void
    {
        $this->ctx->getHttpServerRequest()->setAttribute('attr', 'value');

        static::assertTrue($this->ctx->hasAttr('attr'));
    }

    public function testCookie(): void
    {
        $this->ctx->getHttpServerRequest()->setCookie(new RequestCookie('cookie'));

        static::assertInstanceOf(RequestCookie::class, $this->ctx->cookie('cookie'));
    }

    public function testCookies(): void
    {
        $cookieNames = ['cookie1', 'cookie2', 'cookie3'];

        foreach ($cookieNames as $cookieName) {
            $this->ctx->getHttpServerRequest()->setCookie(new RequestCookie($cookieName));
        }

        $cookies = $this->ctx->cookies();

        foreach ($cookies as $cookieName => $cookie) {
            static::assertInstanceOf(RequestCookie::class, $this->ctx->cookie($cookieName));
        }
    }

    public function testResponseCookie(): void
    {
        $this->ctx->responseCookie('response_cookie', 'value');
        $cookie = $this->ctx->getHttpServerResponse()->getCookie('response_cookie');

        static::assertInstanceOf(ResponseCookie::class, $cookie);
        static::assertEquals('value', $cookie->getValue());
    }

    public function testRemoveCookie(): void
    {
        $this->ctx->responseCookie('response_cookie');
        $this->ctx->removeCookie('response_cookie');
        $cookie = $this->ctx->getHttpServerResponse()->getCookie('response_cookie');

        static::assertNull($cookie);
    }

    public function testPort(): void
    {
        static::assertEquals(1234, $this->ctx->port());
    }

    public function testHost(): void
    {
        static::assertEquals('example.com', $this->ctx->host());
    }

    public function testMethod(): void
    {
        static::assertEquals('GET', $this->ctx->method());
    }

    public function testUserAgent(): void
    {
        $this->ctx->getHttpServerRequest()->setHeader('User-Agent', 'Empress Client');

        static::assertEquals('Empress Client', $this->ctx->userAgent());
    }

    public function testRedirect(): void
    {
        $this->ctx->redirect('/redirect', Status::MOVED_PERMANENTLY);
        $res = $this->ctx->getHttpServerResponse();

        static::assertEquals('/redirect', $res->getHeader('location'));
        static::assertEquals(Status::MOVED_PERMANENTLY, $res->getStatus());
    }

    public function testStatus(): void
    {
        $this->ctx->status(Status::METHOD_NOT_ALLOWED);

        static::assertEquals(Status::METHOD_NOT_ALLOWED, $this->ctx->getHttpServerResponse()->getStatus());
    }

    public function testContentType(): void
    {
        $this->ctx->contentType('application/json');
        $contentType = $this->ctx->getHttpServerResponse()->getHeader('Content-Type');

        static::assertEquals('application/json', $contentType);
    }

    public function testHeader(): void
    {
        $this->ctx->header('Server', 'amphp/http-server');

        static::assertEquals('amphp/http-server', $this->ctx->getHttpServerResponse()->getHeader('Server'));
    }

    public function testRemoveHeader(): void
    {
        $this->ctx->header('Server', 'amphp/http-server');
        $this->ctx->removeHeader('Server');

        static::assertNull($this->ctx->getHttpServerResponse()->getHeader('Server'));
    }

    public function testRequestHeader(): void
    {
        $this->ctx->getHttpServerRequest()->setHeader('X-Custom', 'foo');

        static::assertEquals('foo', $this->ctx->requestHeader('X-Custom'));
    }

    public function testRespond(): Generator
    {
        $this->ctx->response('Hello');
        $body = yield $this->ctx->getHttpServerResponse()->getBody()->read();

        static::assertEquals('Hello', $body);
    }

    public function testResponseBody(): void
    {
        $this->ctx->response('Foo bar');

        static::assertEquals('Foo bar', $this->ctx->responseBody());
    }


    public function testHtml(): Generator
    {
        $this->ctx->html('<h1>Empress</h1>');
        $res = $this->ctx->getHttpServerResponse();
        $body = yield $res->getBody()->read();

        static::assertEquals('<h1>Empress</h1>', $body);
        static::assertEquals('text/html', $res->getHeader('Content-Type'));
    }

    public function testJson(): Generator
    {
        $data = ['status' => 'ok'];
        $encoded = \json_encode($data, JSON_THROW_ON_ERROR);

        $this->ctx->json($data);

        $body = yield $this->ctx->getHttpServerResponse()->getBody()->read();
        $contentType = $this->ctx->getHttpServerResponse()->getHeader('Content-Type');

        static::assertEquals($body, $encoded);
        static::assertEquals('application/json', $contentType);
    }

    public function testJsonFailure(): void
    {
        $this->expectException(JsonException::class);

        $this->ctx->json([INF]);
    }

    public function testHalt(): void
    {
        $this->expectException(HaltException::class);

        $this->ctx->halt();
    }

    public function testHaltWithStatus(): Generator
    {
        try {
            $this->ctx->halt(Status::METHOD_NOT_ALLOWED);
        } catch (HaltException $e) {
            $response = $e->toResponse();
            static::assertEquals(Status::METHOD_NOT_ALLOWED, $response->getStatus());
            static::assertEmpty(yield $response->getBody()->read());
        }
    }

    public function testHaltWithCustomBody(): Generator
    {
        try {
            $this->ctx->halt(Status::OK, 'Go away');
        } catch (HaltException $e) {
            $response = $e->toResponse();
            static::assertEquals('Go away', yield $response->getBody()->read());
        }
    }
}
