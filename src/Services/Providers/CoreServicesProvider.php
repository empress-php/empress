<?php

namespace Empress\Services\Providers;

use Pimple\Container;
use Pimple\ServiceProviderInterface;
use Amp\Http\Server\Router;
use Amp\Http\Server\Options;
use Empress\Services\ResponseService;
use Empress\Services\JsonResponseService;

class CoreServicesProvider implements ServiceProviderInterface
{
    public function register(Container $container)
    {
        $this->registerIfNotPresent($container, 'options', function (Container $c) {
            return new Options;
        });

        $this->registerIfNotPresent($container, 'router', function (Container $c) {
            return new Router;
        });

        $this->registerIfNotPresent($container, 'response', function (Container $c) {
            return new ResponseService;
        });

        $this->registerIfNotPresent($container, 'json', function (Container $c) {
            return new JsonResponseService($c['response']);
        });
    }

    private function registerIfNotPresent(Container $container, $id, \Closure $closure)
    {
        if (!isset($container[$id])) {
            $container[$id] = $closure;
        }
    }
}
