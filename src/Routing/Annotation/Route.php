<?php

namespace Empress\Routing\Annotation;

use Doctrine\Common\Annotations\Annotation;
use Doctrine\Common\Annotations\Annotation\Required;
use Doctrine\Common\Annotations\Annotation\Target;

/**
 * @Annotation
 * @Target({"METHOD"})
 */
final class Route
{

    /**
     * @Required
     * @var string
     */
    public $method;

    /**
     * @var string
     */
    public $path;

    /**
     * @var string
     */
    public $transform;
}
