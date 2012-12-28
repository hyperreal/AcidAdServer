<?php

namespace Hyper\AdsBundle\Tests\Entity;

use Hyper\AdsBundle\Entity\Announcement;
use Hyper\AdsBundle\Entity\Banner;
use Hyper\AdsBundle\DBAL\AnnouncementPaymentType;

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

    /**
     * @param \Hyper\AdsBundle\Entity\Announcement $announcement
     *
     * @dataProvider paymentNeededAnnouncementsProvider
     */
    public function testIsPaidExpiredActive(Announcement $announcement)
    {
        $expireDate = new \DateTime('now +2 months');
        $expireDate2 = new \DateTime('now +3 months');

        $announcement->setExpireDate($expireDate);
        $this->assertFalse($announcement->isExpired());
        $this->assertFalse($announcement->isPaid());
        $this->assertFalse($announcement->isActive());

        $announcement->setPaidTo($expireDate);
        $this->assertFalse($announcement->isExpired());
        $this->assertTrue($announcement->isPaid());
        $this->assertTrue($announcement->isActive());

        $announcement->setExpireDate($expireDate2);
        $this->assertFalse($announcement->isExpired());
        $this->assertFalse($announcement->isPaid());
        $this->assertTrue($announcement->isActive());
    }

    public function paymentNeededAnnouncementsProvider()
    {
        $announcement = new Announcement();
        $announcement->setAnnouncementPaymentType(AnnouncementPaymentType::ANNOUNCEMENT_PAYMENT_TYPE_PREMIUM);

        return array(
            array(new Banner()),
            array($announcement)
        );
    }

}
