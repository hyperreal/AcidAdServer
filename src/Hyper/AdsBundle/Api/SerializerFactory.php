<?php

namespace Hyper\AdsBundle\Api;

use Hyper\AdsBundle\Entity\Announcement;
use Hyper\AdsBundle\Exception\InvalidArgumentException;

//@todo factory service!!
class SerializerFactory
{
    public static function getConverterForObject($object)
    {
        if ($object instanceof Announcement) {
            return new AnnouncementArrayConverter();
        } else {
            throw new InvalidArgumentException('No converter for this object');
        }
    }
}
