<?php

declare(strict_types=1);

namespace Empress\Logging;

use Amp\ByteStream\OutputStream;
use Amp\Log\ConsoleFormatter;
use Amp\Log\StreamHandler;
use Monolog\Logger;

final class DefaultLogger extends Logger
{
    public function __construct(string $name, OutputStream $outputStream)
    {
        $logHandler = new StreamHandler($outputStream);
        $logHandler->setFormatter(
            new ConsoleFormatter(
            "[%datetime%] %channel%.%level_name%: %message% %context% %extra%:\r\n",
            'd/M/Y:H:i:s z',
            true,
            true
        )
        );

        parent::__construct($name, [$logHandler]);
    }
}
