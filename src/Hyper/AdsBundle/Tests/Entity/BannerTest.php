<?php

namespace Hyper\AdsBundle\Tests\Entity;

use Hyper\AdsBundle\Entity\Banner;

class BannerTest extends \PHPUnit_Framework_TestCase
{
    public function testSetExpireDate()
    {
        $banner = new Banner();

        $date = new \DateTime();
        $banner->setExpireDate($date);

        $this->assertEquals($date, $banner->getExpireDate());
    }

    /**
     * @expectedException PHPUnit_Framework_Error
     */
    public function testSetInvalidExpireDate()
    {
        $banner = new Banner();
        $banner->setExpireDate('Something');
    }

    public function testBannerIsExpired()
    {
        $banner = new Banner();
        $banner->setExpireDate(new \DateTime('now - 1 month'));
        $this->assertTrue($banner->isExpired());

        $banner->setExpireDate(new \DateTime('now + 1 month'));
        $this->assertFalse($banner->isExpired());
    }
}
