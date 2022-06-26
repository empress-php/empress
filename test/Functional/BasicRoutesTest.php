<?php

declare(strict_types=1);

namespace Empress\Test\Functional;

use Amp\Http\Client\Body\FormBody;
use Amp\Http\Client\Response;
use Amp\Http\Server\FormParser\Form;
use Amp\Http\Status;
use Empress\Application;
use Empress\Context;
use Empress\Routing\Routes;

final class BasicRoutesTest extends FunctionalTestCase
{
    private const PORT = 1234;

    public function testBasicRequest(): \Generator
    {

        /** @var Response $response */
        $response = yield $this->request('/');

        self::assertSame(Status::OK, $response->getStatus());
        self::assertEmpty(yield $response->getBody()->buffer());
    }

    public function testPostForm(): \Generator
    {
        $form = new FormBody();
        $form->addField('field1', 'value1');
        $form->addField('field2', 'value2');

        /** @var Response $response */
        $response = yield $this->request('/form', 'POST', $form);

        self::assertSame(Status::OK, $response->getStatus());
        self::assertSame('value1-value2', yield $response->getBody()->buffer());
    }

    public function testBeforeAfter(): \Generator
    {
        /** @var Response $response */
        $response = yield $this->request('/greet');

        self::assertSame(Status::OK, $response->getStatus());
        self::assertSame('Hello World', yield $response->getBody()->buffer());
    }

    public function testPathParam(): \Generator
    {

        /** @var Response $response */
        $response = yield $this->request('/name/Alex/surname/Goldberg');

        self::assertSame('Hello, Alex Goldberg', yield $response->getBody()->buffer());
    }

    public function testJson(): \Generator
    {

        /** @var Response $response */
        $response = yield $this->request('/json');

        self::assertSame('{"a":"b","c":"d"}', yield $response->getBody()->buffer());
    }

    public function testRedirect(): \Generator
    {

        /** @var Response $response */
        $response = yield $this->request('/redirect-from');

        self::assertSame(Status::UNAUTHORIZED, $response->getStatus());
    }

    protected function getApplication(): Application
    {
        $app = Application::create(self::PORT);

        $app->routes(function (Routes $routes): void {
            $routes->get('/', fn () => null);

            $routes->post('/form', function (Context $ctx) {

                /** @var Form $form */
                $form = yield $ctx->bufferedForm();

                $ctx->response($form->getValue('field1') . '-' . $form->getValue('field2'));
            });

            $routes->beforeAt('/greet', fn (Context $ctx) => $ctx->response('Hello '));
            $routes->get('/greet', fn () => null);
            $routes->afterAt('/greet', function (Context $ctx): void {

                /** @psalm-suppress PossiblyInvalidOperand */
                $ctx->response($ctx->responseBody() . 'World');
            });

            $routes->get('/name/:name/surname/:surname', function (Context $ctx): void {
                $name = $ctx['name']->unsafeUnwrap();
                $surname = $ctx['surname']->unsafeUnwrap();

                $ctx->response("Hello, $name $surname");
            });

            $routes->get('/json', function (Context $ctx): void {
                $ctx->json([
                    'a' => 'b',
                    'c' => 'd',
                ]);
            });

            $routes->get('/redirect-from', fn (Context $ctx) => $ctx->redirect('/redirect-to'));
            $routes->get('/redirect-to', fn (Context $ctx) => $ctx->status(Status::UNAUTHORIZED));
        });

        return $app;
    }

    protected function getHost(): string
    {
        return 'http://0.0.0.0:' . self::PORT;
    }
}
