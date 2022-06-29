<?php

declare(strict_types=1);

namespace Empress\Test\Routing\Mapping\Exception;

use Amp\PHPUnit\AsyncTestCase;
use Empress\Context;
use Empress\Internal\ContextInjector;
use Empress\Routing\Mapping\ContentTypeMatcher;
use Empress\Routing\Mapping\Exception\ExceptionHandler;
use Empress\Routing\Mapping\Exception\ExceptionMapper;
use Empress\Test\Helper\StubRequestTrait;
use Empress\Validation\Registry\ValidatorRegistryInterface;
use Error;
use Exception;
use Generator;

final class ExceptionMapperTest extends AsyncTestCase
{
    use StubRequestTrait;

    private ExceptionMapper $mapper;

    protected function setUp(): void
    {
        $contentTypeMatcher = new ContentTypeMatcher();

        $this->mapper = new ExceptionMapper($contentTypeMatcher);

        parent::setUp();
    }

    public function testHandleRequest(): Generator
    {
        $this->mapper->addHandler(new ExceptionHandler(function (Context $ctx): void {
            $ctx->response('Foo');
        }, Exception::class));

        $request = $this->createStubRequest();
        $validatorRegistry = $this->createMock(ValidatorRegistryInterface::class);

        $context = new Context($request, $validatorRegistry);
        $injector = new ContextInjector($context);
        $injector->setThrowable(new Exception());

        yield $this->mapper->process($injector);

        self::assertSame('Foo', yield $injector->getResponse()->getBody()->read());
    }

    public function testHandleUncaughtException(): Generator
    {
        $this->expectException(Error::class);

        $this->mapper->addHandler(new ExceptionHandler(fn () => null, Exception::class));

        $request = $this->createStubRequest();
        $validatorRegistry = $this->createMock(ValidatorRegistryInterface::class);

        $context = new Context($request, $validatorRegistry);
        $injector = new ContextInjector($context);
        $injector->setThrowable(new Error());

        yield $this->mapper->process($injector);
    }
}
