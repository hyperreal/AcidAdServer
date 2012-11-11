<?php

namespace Hyper\AdsBundle\Tests\Entity;

use Hyper\AdsBundle\Entity\Announcement;

class AnnouncementTest extends \PHPUnit_Framework_TestCase
{
    const AD_ID = 997;
    /** @var \Hyper\AdsBundle\Entity\Announcement */
    private $ad;

    public function setUp()
    {
        $this->ad = new Announcement();
    }

    public function testSetId()
    {
        $this->ad->setId(self::AD_ID);
        $this->assertEquals(self::AD_ID, $this->ad->getId());
    }

}
