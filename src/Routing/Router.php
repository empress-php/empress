<?php

namespace Empress\Routing;

use Amp\Failure;
use Amp\Http\Server\ErrorHandler;
use Amp\Http\Server\Request;
use Amp\Http\Server\RequestHandler;
use Amp\Http\Server\Response;
use Amp\Http\Server\Server;
use Amp\Http\Server\ServerObserver;
use Amp\Http\Server\StaticContent\DocumentRoot;
use Amp\Http\Status;
use Amp\Promise;
use Amp\Success;
use Empress\Context;
use Empress\Exception\HaltException;
use Empress\Internal\ContextInjector;
use Empress\Routing\Exception\ExceptionMapper;
use Empress\Routing\Handler\HandlerCollection;
use Empress\Routing\Handler\HandlerEntry;
use Empress\Routing\Handler\HandlerType;
use Empress\Routing\Status\StatusMapper;
use Error;
use Throwable;
use function Amp\call;

class Router implements RequestHandler, ServerObserver
{
    public const NAMED_PARAMS_ATTR_NAME = self::class . '_namedParams';
    public const WILDCARDS_ATTR_NAME = self::class . '_wildcards';

    private bool $running = false;

    private ErrorHandler $errorHandler;

    private ?DocumentRoot $fallback = null;

    public function __construct(
        private ExceptionMapper $exceptionMapper,
        private StatusMapper $statusMapper,
        private HandlerCollection $handlerCollection
    )
    {
    }

    /**
     * @inheritDoc
     */
    public function handleRequest(Request $request): Promise
    {
        $method = $request->getMethod();
        $path = \rawurldecode($request->getUri()->getPath());

        $entries = $this->handlerCollection->filterByPath($path);

        if ($entries->count() === 0) {
            if ($this->fallback !== null) {
                return $this->fallback->handleRequest($request);
            }

            return $this->handleNotFound($request);
        }

        $handlerType = HandlerType::fromString($method);
        $entries = $entries->filterByType($handlerType);

        $handlerEntry = $entries->first();

        if ($handlerEntry === null) {
            return $this->handleMethodNotAllowed($request);
        }

        return $this->dispatch($request, $handlerEntry, $path);
    }


    public function setFallback(DocumentRoot $requestHandler): void
    {
        if ($this->running) {
            throw new Error('Cannot add fallback request handler after the server has started');
        }

        $this->fallback = $requestHandler;
    }

    public function onStart(Server $server): Promise
    {
        if ($this->running) {
            return new Failure(new Error('Server has already been started'));
        }

        $this->errorHandler = $server->getErrorHandler();

        if ($this->handlerCollection->count() === 0) {
            return new Failure(new Error(
                'Router start failure: no routes registered'
            ));
        }

        if (isset($this->fallback)) {
            return $this->fallback->onStart($server);
        }

        $this->running = true;

        return new Success();
    }

    public function onStop(Server $server): Promise
    {
        if (isset($this->fallback)) {
            return $this->fallback->onStop($server);
        }

        $this->running = false;

        return new Success();
    }

    /**
     * @param Request $request
     * @param HandlerEntry $handlerEntry
     * @param string $path
     * @return Promise<Response>
     */
    private function dispatch(Request $request, HandlerEntry $handlerEntry, string $path): Promise
    {
        return call(function () use ($request, $handlerEntry, $path) {
            $request->setAttribute(self::NAMED_PARAMS_ATTR_NAME, $handlerEntry->getPathMatcher()->extractNamedParams($path));
            $request->setAttribute(self::WILDCARDS_ATTR_NAME, $handlerEntry->getPathMatcher()->extractWildcards($path));

            $context = new Context($request, new Response());
            $injector = new ContextInjector($context);

            try {
                $beforeFilters = $this->handlerCollection
                    ->filterByPath($path)
                    ->filterByType(HandlerType::BEFORE);

                foreach ($beforeFilters as $beforeFilter) {
                    yield $injector->inject($beforeFilter->getHandler());
                }

                yield $injector->inject($handlerEntry->getHandler());

                $afterFilters = $this->handlerCollection
                    ->filterByPath($path)
                    ->filterByType(HandlerType::AFTER);

                foreach ($afterFilters as $afterFilter) {
                    yield $injector->inject($afterFilter->getHandler());
                }

                yield $this->statusMapper->process($injector);
            } catch (HaltException $e) {
                return $e->toResponse();
            } catch (Throwable $e) {
                $injector->setThrowable($e);

                yield $this->exceptionMapper->process($injector);
            }

            return $injector->getResponse();
        });
    }

    private function handleNotFound(Request $request): Promise
    {
        return $this->errorHandler->handleError(Status::NOT_FOUND, null, $request);
    }

    private function handleMethodNotAllowed(Request $request): Promise
    {
        return $this->errorHandler->handleError(Status::METHOD_NOT_ALLOWED, null, $request);
    }
}
