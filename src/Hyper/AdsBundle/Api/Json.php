<?php

namespace Hyper\AdsBundle\Api;

use Doctrine\Common\Annotations\Annotation;
use Doctrine\Common\Annotations\Annotation\Target;

/**
 * @Annotation
 * @Target("METHOD")
 */
class Json
{
    /** @var boolean */
    public $full;
}
