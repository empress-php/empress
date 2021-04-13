<?php

namespace Empress\Middleware;

use Amp\Http\Server\Middleware;
use Amp\Http\Server\Request;
use Amp\Http\Server\RequestHandler;
use Amp\Http\Server\Response;
use Amp\Http\Status;
use Amp\Promise;
use function Amp\call;

class StripTrailingSlashMiddleware implements Middleware
{
    public function handleRequest(Request $request, RequestHandler $requestHandler): Promise
    {
        return call(function () use ($request, $requestHandler) {
            $uri = $request->getUri();
            $path = $uri->getPath();

            if ($path === '/' || !str_ends_with($path, '/')) {
                return yield $requestHandler->handleRequest($request);
            }

            $path = rtrim($path, '/');
            $uri = $uri->withPath($path);

            if ($request->getMethod() === 'GET') {
                $response = new Response();
                $response->addHeader('Location', (string) $uri);
                $response->setStatus(Status::PERMANENT_REDIRECT);

                return $response;
            }

            $request->setUri($uri);

            return yield $requestHandler->handleRequest($request);
        });
    }
}