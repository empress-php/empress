<?php

namespace Empress\Routing;

use Amp\Failure;
use Amp\Http\Server\ErrorHandler;
use Amp\Http\Server\Request;
use Amp\Http\Server\RequestHandler;
use Amp\Http\Server\Server;
use Amp\Http\Server\ServerObserver;
use Amp\Http\Server\StaticContent\DocumentRoot;
use Amp\Http\Status;
use Amp\Promise;
use Amp\Success;
use Empress\Exception\HaltException;
use Empress\Internal\ContextInjector;
use Empress\Routing\Exception\ExceptionHandler;
use Empress\Routing\Exception\ExceptionMapper;
use Empress\Routing\Status\StatusHandler;
use Empress\Routing\Status\StatusMapper;
use Error;
use Throwable;
use function Amp\call;

class Router implements RequestHandler, ServerObserver
{

    /**
     * @var bool
     */
    private $running = false;

    /**
     * @var ErrorHandler
     */
    private $errorHandler;

    /**
     * @var DocumentRoot|null
     */
    private $fallback;

    /**
     * @var ExceptionMapper
     */
    private $exceptionMapper;

    /**
     * @var StatusMapper
     */
    private $statusMapper;

    /**
     * @var PathMatcher
     */
    private $pathMatcher;

    public function __construct()
    {
        $this->exceptionMapper = new ExceptionMapper();
        $this->statusMapper = new StatusMapper();
        $this->pathMatcher = new PathMatcher();
    }

    /**
     * @inheritDoc
     */
    public function handleRequest(Request $request): Promise
    {
        $method = $request->getMethod();
        $path = rawurldecode($request->getUri()->getPath());

        var_dump($path);

        $entries = $this->pathMatcher->findEntries(HandlerType::fromString($method), $path);

        /** @var HandlerEntry $handlerEntry */
        $handlerEntry = reset($entries);

        if ($handlerEntry === false) {
            if ($this->fallback !== null) {
                return $this->fallback->handleRequest($request);
            }

            return $this->handleNotFound($request);
        }

        return $this->handleFound($request, $handlerEntry, $path);
    }

    private function handleFound(Request $request, HandlerEntry $handlerEntry, string $path): Promise
    {
        return call(function () use ($request, $handlerEntry, $path) {
            try {
                $request->setAttribute(Router::class, $this->pathMatcher->getPathParams($handlerEntry, $path));

                $beforeFilters = $this->pathMatcher->findEntries(HandlerType::BEFORE, $path);

                /** @var HandlerEntry $beforeFilter */
                foreach ($beforeFilters as $beforeFilter) {
                    $injector = new ContextInjector($beforeFilter->getHandler(), $request);

                    yield $injector->inject();
                }

                $injector = new ContextInjector($handlerEntry->getHandler(), $request);
                $response = yield $injector->inject();

                $afterFilters = $this->pathMatcher->findEntries(HandlerType::AFTER, $path);

                /** @var HandlerEntry $afterFilter */
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

    public function addExceptionHandler(ExceptionHandler $handler): void
    {
        if ($this->running) {
            throw new Error('Cannot add exception handlers after the server has started');
        }

        $this->exceptionMapper->addHandler($handler);
    }

    public function addStatusHandler(StatusHandler $handler): void
    {
        if ($this->running) {
            throw new Error('Cannot add status handlers after the server has started');
        }

        $this->statusMapper->addHandler($handler);
    }

    public function addEntries(HandlerGroup $group): void
    {
        if ($this->running) {
            throw new Error('Cannot add routes after the server has started');
        }

        $this->pathMatcher->addEntries($group);
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
            return new Failure(new Error('Router already started'));
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

        return new Success();
    }

    public function onStop(Server $server): Promise
    {
        $this->pathMatcher = null;
        $this->exceptionMapper = null;
        $this->statusMapper = null;

        return $this->fallback->onStop($server);
    }
}
