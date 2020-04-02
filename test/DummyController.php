<?php

namespace Empress\Test;

use Amp\Http\Status;
use Empress\Context;

class DummyController
{
    public function dummy(Context $ctx)
    {
        $ctx->status(Status::UNAUTHORIZED);
    }
}
