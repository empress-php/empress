<?php

namespace Empress\Provider;

use Pimple\Container;
use Pimple\ServiceProviderInterface;
use Amp\Http\Server\Router;
use Amp\Http\Server\Options;
use Empress\Services\FormParserService;

class CoreServicesProvider implements ServiceProviderInterface
{
    public function register(Container $container)
    {
        $this->registerIfNotPresent($container, 'options', function () {
            $options = new Options;

            return $options
                ->withoutDebugMode();
        });

        $this->registerIfNotPresent($container, 'router', function () {
            return new Router;
        });
    }

    private function registerIfNotPresent(Container $container, $id, \Closure $closure)
    {
        if (!isset($container[$id])) {
            $container[$id] = $closure;
        }
    }
}
