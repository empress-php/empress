<?php

namespace Empress\Routing\Annotation;

use Doctrine\Common\Annotations\Annotation;
use Doctrine\Common\Annotations\Annotation\Required;
use Doctrine\Common\Annotations\Annotation\Target;

/**
 * @Annotation
 * @Target({"CLASS"})
 */
final class Mount
{

    /**
     * @Required
     * @var string
     */
    public $path;
}
