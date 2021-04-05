<?php

namespace Empress\Routing;

use Amp\Promise;
use Empress\Internal\ContextInjector;

interface MapperInterface
{
    public function process(ContextInjector $injector): Promise;
}
