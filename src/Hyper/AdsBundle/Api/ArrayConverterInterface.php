<?php

namespace Hyper\AdsBundle\Api;

//@todo factory service!!
interface ArrayConverterInterface
{
    public function toArray($object, $full = false);
}
