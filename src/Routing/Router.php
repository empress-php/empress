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
use Empress\Exception\HaltException;
use Empress\Internal\ContextInjector;
use Empress\Routing\Exception\ExceptionMapper;
use Empress\Routing\Status\StatusMapper;
use Error;
use Throwable;
use function Amp\call;

class Router implements RequestHandler, ServerObserver
{
    private bool $running = false;

    private ErrorHandler $errorHandler;

    private ?DocumentRoot $fallback = null;

    private ExceptionMapper $exceptionMapper;

    private StatusMapper $statusMapper;

    private PathMatcher $pathMatcher;

    public function __construct(ExceptionMapper $exceptionMapper, StatusMapper $statusMapper, PathMatcher $pathMatcher)
    {
        $this->exceptionMapper = $exceptionMapper;
        $this->statusMapper = $statusMapper;
        $this->pathMatcher = $pathMatcher;
    }

    /**
     * @inheritDoc
     */
    public function handleRequest(Request $request): Promise
    {
        $method = $request->getMethod();
        $path = rawurldecode($request->getUri()->getPath());
        $entries = $this->pathMatcher->findEntries($path);

        if (empty($entries)) {
            if ($this->fallback !== null) {
                return $this->fallback->handleRequest($request);
            }

            return $this->handleNotFound($request);
        }

        $entries = array_filter($entries, function (HandlerEntry $entry) use ($method) {
            return $entry->getType() === HandlerType::fromString($method);
        });

        /** @var HandlerEntry|bool $handlerEntry */
        $handlerEntry = reset($entries);

        if ($handlerEntry === false) {
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

        if (!$this->pathMatcher->hasEntries()) {
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
            try {
                $request->setAttribute(Router::class, $this->pathMatcher->getPathParams($handlerEntry, $path));

                $beforeFilters = $this->pathMatcher->findEntries($path, HandlerType::BEFORE);

                foreach ($beforeFilters as $beforeFilter) {
                    $injector = new ContextInjector($beforeFilter->getHandler(), $request);

                    $response = yield $injector->inject();
                }

                $injector = new ContextInjector($handlerEntry->getHandler(), $request, $response ?? null);
                $response = yield $injector->inject();

                $afterFilters = $this->pathMatcher->findEntries($path, HandlerType::AFTER);

                foreach ($afterFilters as $afterFilter) {
                    $injector = new ContextInjector($afterFilter->getHandler(), $request, $response);

                    $response = yield $injector->inject();
                }

                return yield $this->statusMapper->process($request, $response);
            } catch (HaltException $e) {
                return $e->toResponse();
            } catch (Throwable $e) {
                return yield $this->exceptionMapper->process($e, $request);
            }
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
