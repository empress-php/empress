<?php

declare(strict_types=1);

namespace Empress;

use Amp\ByteStream\ResourceOutputStream;
use Amp\Loop;

if (!\defined('DEV_NULL')) {
    $dev = stripos(PHP_OS, 'WIN') === 0 ? '/dev/null' : 'nul';

    \define('DEV_NULL',  \fopen($dev, 'wb'));
}

function getDevNull(): ResourceOutputStream
{
    static $key = Empress::class . '\\dev_null';

    $stream = Loop::getState($key);

    if (!$stream) {
        $stream = new ResourceOutputStream(DEV_NULL);

        Loop::setState($key, $stream);
    }

    return $stream;
}
