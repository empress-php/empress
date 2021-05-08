<?php

namespace Empress\Test\Functional;

use Amp\Http\Client\Body\FormBody;
use Amp\Http\Client\Response;
use Amp\Http\Server\FormParser\Form;
use Amp\Http\Status;
use Empress\Application;
use Empress\Context;
use Empress\Routing\Routes;

class BasicRoutesTest extends FunctionalTestCase
{
    private const PORT = 1234;

    public function testBasicRequest(): \Generator
    {

        /** @var Response $response */
        $response = yield $this->request('/');

        static::assertEquals(Status::OK, $response->getStatus());
        static::assertEmpty(yield $response->getBody()->buffer());
    }

    public function testPostForm(): \Generator
    {
        $form = new FormBody();
        $form->addField('field1', 'value1');
        $form->addField('field2', 'value2');

        /** @var Response $response */
        $response = yield $this->request('/form', 'POST', $form);

        static::assertEquals(Status::OK, $response->getStatus());
        static::assertEquals('value1-value2', yield $response->getBody()->buffer());
    }

    public function testBeforeAfter(): \Generator
    {
        /** @var Response $response */
        $response = yield $this->request('/greet');

        static::assertEquals(Status::OK, $response->getStatus());
        static::assertEquals('Hello World', yield $response->getBody()->buffer());
    }

    public function testPathParam(): \Generator
    {

        /** @var Response $response */
        $response = yield $this->request('/name/Alex/surname/Goldberg');

        static::assertEquals('Hello, Alex Goldberg', yield $response->getBody()->buffer());
    }

    public function testJson(): \Generator
    {

        /** @var Response $response */
        $response = yield $this->request('/json');

        static::assertEquals('{"a":"b","c":"d"}', yield $response->getBody()->buffer());
    }

    public function testRedirect(): \Generator
    {

        /** @var Response $response */
        $response = yield $this->request('/redirect-from');

        static::assertEquals(Status::UNAUTHORIZED, $response->getStatus());
    }

    protected function getApplication(): Application
    {
        $app = Application::create(self::PORT);

        $app->routes(function (Routes $routes) {
            $routes->get('/', fn () => null);

            $routes->post('/form', function (Context $ctx) {

                /** @var Form $form */
                $form = yield $ctx->bufferedForm();

                $ctx->response($form->getValue('field1') . '-' . $form->getValue('field2'));
            });

            $routes->beforeAt('/greet', fn (Context $ctx) => $ctx->response('Hello '));
            $routes->get('/greet', fn () => null);
            $routes->afterAt('/greet', function (Context $ctx) {

                /** @psalm-suppress PossiblyInvalidOperand */
                $ctx->response($ctx->responseBody() . 'World');
            });

            $routes->get('/name/:name/surname/:surname', function (Context $ctx) {
                $name = $ctx['name']->unsafeUnwrap();
                $surname = $ctx['surname']->unsafeUnwrap();

                $ctx->response("Hello, $name $surname");
            });

            $routes->get('/json', function (Context $ctx) {
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
