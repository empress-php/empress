<?php

namespace Empress\Provider;

use Amp\Http\Server\FormParser\BufferingParser;
use Amp\Http\Server\FormParser\StreamingParser;
use Pimple\ServiceProviderInterface;
use Pimple\Container;

class FormParserServiceProvider extends ServiceProviderInterface
{
    public function register(Container $container)
    {
        $container['form.parser'] = function () {
            return new BufferingParser;
        };

        $container['form.streaming_parser'] = function () {
            return new StreamingParser;
        }
    }
}
