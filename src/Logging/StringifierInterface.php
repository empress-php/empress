<?php

declare(strict_types=1);

namespace Empress\Logging;

use Amp\Promise;

interface StringifierInterface
{
    public const MAX_BODY_LENGTH = 100;

    /**
     * Returns a textual representation of a loggable object.
     *
     * @return Promise<string>
     */
    public function stringify(): Promise;
}
