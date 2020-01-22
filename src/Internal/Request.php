<?php

namespace Empress\Internal;

use Amp\Http\Server\Request as HttpRequest;
use Amp\Http\Server\Router;
use Amp\Http\Server\Session\Session;

final class Request
{
    /** @var HttpRequest */
    private $request;

    /** @var array */
    private $params;

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

    /**
     * @inheritDoc
     */
    public function __call(string $name, array $arguments)
    {
        return ([$this->request, $name])($arguments);
    }
}
