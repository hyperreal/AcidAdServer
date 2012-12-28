<?php

namespace Hyper\AdsBundle\Helper;

use Hyper\AdsBundle\Entity\Banner;
use Hyper\AdsBundle\DBAL\BannerType;

class BannerTypeDeterminer
{
    const EXTENSION_SWF = 'swf';
    private static $validImageExtensions = array('jpg', 'jpeg', 'png', 'gif');

    private $banner;

    public function __construct(Banner $banner)
    {
        $this->banner = $banner;
    }

    public function getType()
    {
        $extension = strtolower(pathinfo($this->banner->getOriginalFileName(), PATHINFO_EXTENSION));

        if (self::EXTENSION_SWF === $extension) {
            return BannerType::BANNER_TYPE_FLASH;
        } elseif (in_array($extension, self::$validImageExtensions)) {
            return BannerType::BANNER_TYPE_IMAGE;
        }

        return BannerType::BANNER_TYPE_TEXT;
    }
}
