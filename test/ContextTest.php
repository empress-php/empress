<?php

declare(strict_types=1);

namespace Empress\Test;

use Amp\ByteStream\InputStream;
use Amp\Http\Cookie\RequestCookie;
use Amp\Http\Cookie\ResponseCookie;
use Amp\Http\Server\Response;
use Amp\Http\Server\Session\Session;
use Amp\Http\Status;
use Amp\PHPUnit\AsyncTestCase;
use Amp\Success;
use Empress\Context;
use Empress\Routing\HaltException;
use Empress\Test\Helper\SimpleForm;
use Empress\Test\Helper\SimpleFormValidator;
use Empress\Test\Helper\StubRequestTrait;
use Empress\Validation\Registry\DefaultValidatorRegistry;
use Empress\Validation\ValidationContext;
use Generator;
use JsonException;
use LogicException;
use const INF;

final class ContextTest extends AsyncTestCase
{
    use StubRequestTrait;

    private Context $ctx;

    public function setUp(): void
    {

        // Set example params
        $request = $this->createStubRequest('GET', '/?a=b&c=d', [
            'param1' => 'value1',
            'param2' => 'value2',
        ], [
            'abc',
            'def',
        ]);

        $validatorRegistry = new DefaultValidatorRegistry();
        $validatorRegistry->register(SimpleForm::class, new SimpleFormValidator($validatorRegistry));

        $this->ctx = new Context($request, $validatorRegistry, new Response());

        parent::setUp();
    }

    public function testOffsets(): void
    {
        self::assertSame('value1', $this->ctx['param1']->unsafeUnwrap());
        self::assertSame('value2', $this->ctx['param2']->unsafeUnwrap());
        self::assertTrue(isset($this->ctx['param1']));
        self::assertFalse(isset($this->ctx['param3']));
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

    public function testBufferedBody(): Generator
    {
        $body = 'Hello';
        $this->ctx->getHttpServerRequest()->setBody($body);

        $contents = yield $this->ctx->bufferedBody();

        self::assertSame($body, $contents);
    }

    public function testValidatedBody(): Generator
    {
        $requestBody = $this->createMock(InputStream::class);
        $requestBody
            ->expects(self::atLeastOnce())
            ->method('read')
            ->willReturnOnConsecutiveCalls(new Success('{"a":123,"b":456}'), new Success());

        $this->ctx->getHttpServerRequest()->setBody($requestBody);

        /** @var ValidationContext $validated */
        $validated = yield $this->ctx->validatedBody();
        $body = $validated
            ->pass()
            ->unwrap();

        self::assertSame('{"a":123,"b":456}', $body);
    }

    public function testValidatedForm(): Generator
    {
        $this->ctx->getHttpServerRequest()->setHeader('Content-Type', 'application/x-www-form-urlencoded');
        $this->ctx->getHttpServerRequest()->setBody('field1=100');

        /** @var ValidationContext $form */
        $form = yield $this->ctx->validatedForm();

        /** @var SimpleForm $simpleForm */
        $simpleForm = $form->to(SimpleForm::class)->unwrap();

        self::assertSame(100, $simpleForm->field);
    }

    public function testQueryString(): void
    {
        $queryString = $this->ctx->queryString();

        self::assertSame('a=b&c=d', $queryString);
    }

    public function testQueryArray(): void
    {
        $queryArray = $this->ctx->queryArray();

        self::assertSame([
            'a' => 'b',
            'c' => 'd',
        ], $queryArray);
    }

    public function testSession(): void
    {
        self::assertInstanceOf(Session::class, $this->ctx->session());

        // Always return the same instance
        self::assertSame($this->ctx->session(), $this->ctx->session());
    }

    public function testAttr(): void
    {
        $this->ctx->getHttpServerRequest()->setAttribute('attr', 'value');
        $value = $this->ctx->attr('attr');

        self::assertSame('value', $value);
    }

    public function testHasAttr(): void
    {
        $this->ctx->getHttpServerRequest()->setAttribute('attr', 'value');

        self::assertTrue($this->ctx->hasAttr('attr'));
    }

    public function testCookie(): void
    {
        $this->ctx->getHttpServerRequest()->setCookie(new RequestCookie('cookie'));

        self::assertInstanceOf(RequestCookie::class, $this->ctx->cookie('cookie'));
    }

    public function testCookies(): void
    {
        $cookieNames = ['cookie1', 'cookie2', 'cookie3'];

        foreach ($cookieNames as $cookieName) {
            $this->ctx->getHttpServerRequest()->setCookie(new RequestCookie($cookieName));
        }

        $cookies = $this->ctx->cookies();

        foreach ($cookies as $cookieName => $cookie) {
            self::assertInstanceOf(RequestCookie::class, $this->ctx->cookie($cookieName));
        }
    }

    public function testResponseCookie(): void
    {
        $this->ctx->responseCookie('response_cookie', 'value');
        $cookie = $this->ctx->getHttpServerResponse()->getCookie('response_cookie');

        self::assertInstanceOf(ResponseCookie::class, $cookie);
        self::assertSame('value', $cookie->getValue());
    }

    public function testRemoveCookie(): void
    {
        $this->ctx->responseCookie('response_cookie');
        $this->ctx->removeCookie('response_cookie');
        $cookie = $this->ctx->getHttpServerResponse()->getCookie('response_cookie');

        self::assertNull($cookie);
    }

    public function testPort(): void
    {
        self::assertSame(1234, $this->ctx->port());
    }

    public function testHost(): void
    {
        self::assertSame('example.com', $this->ctx->host());
    }

    public function testMethod(): void
    {
        self::assertSame('GET', $this->ctx->method());
    }

    public function testUserAgent(): void
    {
        $this->ctx->getHttpServerRequest()->setHeader('User-Agent', 'Empress Client');

        self::assertSame('Empress Client', $this->ctx->userAgent());
    }

    public function testRedirect(): void
    {
        $this->ctx->redirect('/redirect', Status::MOVED_PERMANENTLY);
        $res = $this->ctx->getHttpServerResponse();

        self::assertSame('/redirect', $res->getHeader('location'));
        self::assertSame(Status::MOVED_PERMANENTLY, $res->getStatus());
    }

    public function testStatus(): void
    {
        $this->ctx->status(Status::METHOD_NOT_ALLOWED);

        self::assertSame(Status::METHOD_NOT_ALLOWED, $this->ctx->getHttpServerResponse()->getStatus());
    }

    public function testContentType(): void
    {
        $this->ctx->contentType('application/json');
        $contentType = $this->ctx->getHttpServerResponse()->getHeader('Content-Type');

        self::assertSame('application/json', $contentType);
    }

    public function testHeader(): void
    {
        $this->ctx->header('Server', 'amphp/http-server');

        self::assertSame('amphp/http-server', $this->ctx->getHttpServerResponse()->getHeader('Server'));
    }

    public function testRemoveHeader(): void
    {
        $this->ctx->header('Server', 'amphp/http-server');
        $this->ctx->removeHeader('Server');

        self::assertNull($this->ctx->getHttpServerResponse()->getHeader('Server'));
    }

    public function testRequestHeader(): void
    {
        $this->ctx->getHttpServerRequest()->setHeader('X-Custom', 'foo');

        self::assertSame('foo', $this->ctx->requestHeader('X-Custom'));
    }

    public function testRespond(): Generator
    {
        $this->ctx->response('Hello');
        $body = yield $this->ctx->getHttpServerResponse()->getBody()->read();

        self::assertSame('Hello', $body);
    }

    public function testResponseBody(): void
    {
        $this->ctx->response('Foo bar');

        self::assertSame('Foo bar', $this->ctx->responseBody());
    }

    public function testHtml(): Generator
    {
        $this->ctx->html('<h1>Empress</h1>');
        $res = $this->ctx->getHttpServerResponse();
        $body = yield $res->getBody()->read();

        self::assertSame('<h1>Empress</h1>', $body);
        self::assertSame('text/html', $res->getHeader('Content-Type'));
    }

    public function testJson(): Generator
    {
        $data = ['status' => 'ok'];
        $encoded = \json_encode($data, \JSON_THROW_ON_ERROR);

        $this->ctx->json($data);

        $body = yield $this->ctx->getHttpServerResponse()->getBody()->read();
        $contentType = $this->ctx->getHttpServerResponse()->getHeader('Content-Type');

        self::assertSame($body, $encoded);
        self::assertSame('application/json', $contentType);
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
            self::assertSame(Status::METHOD_NOT_ALLOWED, $response->getStatus());
            self::assertEmpty(yield $response->getBody()->read());
        }
    }

    public function testHaltWithCustomBody(): Generator
    {
        try {
            $this->ctx->halt(Status::OK, 'Go away');
        } catch (HaltException $e) {
            $response = $e->toResponse();
            self::assertSame('Go away', yield $response->getBody()->read());
        }
    }

    public function testWildcards(): void
    {
        $wildcards = $this->ctx->wildcards();

        self::assertSame(['abc', 'def'], $wildcards);
    }
}
