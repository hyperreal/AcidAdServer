<?php

namespace Hyper\AdsBundle\Api;

interface EntitySerializerInterface
{
    public function toJsonArray(array $objects);
    public function toJson($object, $full = true);
}
