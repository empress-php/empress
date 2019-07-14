<?php

namespace Empress\Services;

use Amp\ByteStream\ResourceOutputStream;
use Amp\Log\ConsoleFormatter;
use Amp\Log\StreamHandler;
use Monolog\Logger;
use Pimple\Container;

class LoggerService extends AbstractService
{
    public function getServiceObject()
    {
        $logHandler = new StreamHandler(new ResourceOutputStream(\STDOUT));
        $logHandler->setFormatter(new ConsoleFormatter);
        $this->logger = new Logger('server');
        $this->logger->pushHandler($logHandler);

        return $this->logger;
    }
}
