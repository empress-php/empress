<?php

namespace Empress\Routing\RouteCollector\Attribute;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
final class Group
{
    public function __construct(private string $prefix)
    {
    }

    public function getPrefix(): string
    {
        return $this->prefix;
    }
}
