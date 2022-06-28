<?php

namespace Empress;

use Amp\ByteStream\ResourceOutputStream;
use Amp\Loop;

if (!\defined('DEV_NULL')) {
    \define('DEV_NULL', \fopen('/dev/null', 'w'));
}

function getDevNull(): ResourceOutputStream
{
    static $key = Empress::class . '\\dev_null';

    $stream = Loop::getState($key);

    if (!$stream) {
        $stream = new ResourceOutputStream(\DEV_NULL);

        Loop::setState($key, $stream);
    }

    return $stream;
}
