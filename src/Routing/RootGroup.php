<?php

namespace Empress\Routing;

class RootGroup extends HandlerGroup
{
    public function __construct()
    {
        parent::__construct('/');
    }
}
