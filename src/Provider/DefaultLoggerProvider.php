<?php

namespace Empress\Provider;

use Amp\ByteStream\ResourceOutputStream;
use Amp\Log\ConsoleFormatter;
use Amp\Log\StreamHandler;
use Monolog\Logger;
use Pimple\Container;
use Pimple\ServiceProviderInterface;
use Psr\Log\LoggerInterface;

class DefaultLoggerProvider implements ServiceProviderInterface
{
    public function register(Container $container)
    {
        $logHandler = new StreamHandler(new ResourceOutputStream(\STDOUT));
        $logHandler->setFormatter(new ConsoleFormatter);
        $logger = new Logger('server');
        $logger->pushHandler($logHandler);

        $container['logger'] = $logger;
    }
}
