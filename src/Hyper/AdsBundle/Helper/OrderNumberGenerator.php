<?php

namespace Hyper\AdsBundle\Helper;

use Doctrine\ORM\EntityManager;
use Hyper\AdsBundle\Entity\Advertiser;
use Hyper\AdsBundle\Entity\Banner;
use Hyper\AdsBundle\Entity\Zone;

class OrderNumberGenerator
{
    const PREFIX_BANNER = 'B';

    public function getBannerPaymentOrderNumber(Banner $banner, Advertiser $advertiser, Zone $zone = null)
    {
        $items = array();

        $items[] = self::PREFIX_BANNER . $banner->getId();
        $items[] = $advertiser->getId();

        if (null !== $zone) {
            $items[] = $zone->getId();
        }

        $items[] = (int)mt_rand(0, 1000);

        return implode('-', $items);
    }
}
