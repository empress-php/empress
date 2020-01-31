<?php

namespace Empress\Internal;

use Amp\Http\Server\Request as HttpRequest;
use Amp\Http\Server\Router;
use Amp\Http\Server\Session\Session;
use Amp\Promise;
use function Amp\call;

class Request
{
    /** @var HttpRequest */
    private $request;

    /** @var array */
    private $params;

    /** @var array */
    private $postParams = [];

    /**
     * Request constructor.
     * @param HttpRequest $request
     */
    public function __construct(HttpRequest $request)
    {
        $this->request = $request;
        $this->params = $request->getAttribute(Router::class);
    }

    /**
     * @return array|mixed
     */
    public function getParams()
    {
        return $this->params;
    }

    /**
     * @return Promise<array>
     */
    public function getParsedBody(): Promise
    {
        return call(function () {
           $body = $this->request->getBody()->read();
           parse_str(yield $body, $params);

           return $params;
        });
    }

    /**
     * @param string $name
     * @return mixed|null
     */
    public function getParam(string $name)
    {
        return $this->params[$name] ?? null;
    }

    /**
     * @return mixed
     */
    public function getSession()
    {
        return $this->request->getAttribute(Session::class);
    }

    public function __call($name, $arguments)
    {
        return ([$this->request, $name])($arguments);
    }
}
