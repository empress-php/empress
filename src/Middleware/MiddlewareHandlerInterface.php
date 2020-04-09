<?php

namespace Empress\Middleware;

interface MiddlewareHandlerInterface
{
    public function getCallable(): callable;
}
