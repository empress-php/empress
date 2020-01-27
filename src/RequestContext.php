<?php

namespace Empress;

use Amp\Http\Server\Request;
use Amp\Http\Server\Router;
use Amp\Http\Server\Session\Session;

class RequestContext
{
    /** @var Request */
    private $request;

    /** @var array */
    private $params;

    /**
     * RequestContext constructor.
     * @param Request $request
     */
    public function __construct(Request $request)
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
     * @return Request
     */
    public function getRequest(): Request
    {
        return $this->request;
    }
}
