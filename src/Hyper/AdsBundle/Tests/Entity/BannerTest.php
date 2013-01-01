<?php

namespace Hyper\AdsBundle\Tests\Entity;

use Hyper\AdsBundle\Entity\Banner;
use Hyper\AdsBundle\DBAL\AnnouncementPaymentType;

class BannerTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Hyper\AdsBundle\Entity\Banner */
    private $banner;

    public function setUp()
    {
        $this->banner = new Banner();
    }

    public function testValidInitialization()
    {
        $this->assertAttributeEquals(
            AnnouncementPaymentType::ANNOUNCEMENT_PAYMENT_TYPE_PREMIUM,
            'announcementPaymentType',
            $this->banner
        );

        $this->assertAttributeInstanceOf('Doctrine\Common\Collections\Collection', 'zones', $this->banner);
        $this->assertAttributeEquals('text', 'type', $this->banner);
    }
}
