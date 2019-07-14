<?php

namespace Empress\Services;

use Pimple\Container;

class DefaultServicesProvider extends AbstractServiceProvider
{
    public function register(Container $container)
    {
        isset($container['logger']) ?: $this->addService($container, 'logger', LoggerService::class);
        isset($container['response']) ?: $this->addService($container, 'response', ResponseService::class);
        isset($container['response.json']) ?: $this->addService($container, 'response.json', JsonResponseService::class);
    }
}
