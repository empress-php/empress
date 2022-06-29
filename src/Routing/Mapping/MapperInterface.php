<?php

declare(strict_types=1);

namespace Empress\Routing\Mapping;

use Amp\Promise;
use Empress\Internal\ContextInjector;

interface MapperInterface
{
    public function process(ContextInjector $injector): Promise;
}
