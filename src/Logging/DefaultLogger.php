<?php

namespace Empress\Logging;

use Amp\ByteStream\OutputStream;
use Amp\Log\ConsoleFormatter;
use Amp\Log\StreamHandler;
use Monolog\Logger;

class DefaultLogger extends Logger
{
    public function __construct(string $name, OutputStream $outputStream)
    {
        $logHandler = new StreamHandler($outputStream);
        $logHandler->setFormatter(new ConsoleFormatter(null, null, true, true));

        parent::__construct($name, [$logHandler]);
    }
}
