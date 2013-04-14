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

    /**
     * @covers \Hyper\AdsBundle\Entity\Advertisement::__construct
     */
    public function testValidInitialization()
    {
        $this->assertAttributeInstanceOf('Doctrine\Common\Collections\Collection', 'orders', $this->ad);
    }

    /**
     * @covers \Hyper\AdsBundle\Entity\Advertisement::setId
     * @covers \Hyper\AdsBundle\Entity\Advertisement::getId
     */
    public function testSetId()
    {
        $this->ad->setId(self::AD_ID);
        $this->assertEquals(self::AD_ID, $this->ad->getId());
    }

    /**
     * @covers \Hyper\AdsBundle\Entity\Advertisement::setTitle
     * @covers \Hyper\AdsBundle\Entity\Advertisement::getTitle
     */
    public function testSetTitle()
    {
        $this->ad->setTitle('title');
        $this->assertEquals('title', $this->ad->getTitle());
    }

    /**
     * @covers \Hyper\AdsBundle\Entity\Advertisement::isActive
     */
    public function testIsActiveForStandardAd()
    {
        $this->ad->setAnnouncementPaymentType(AnnouncementPaymentType::ANNOUNCEMENT_PAYMENT_TYPE_STANDARD);
        $this->assertTrue($this->ad->isActive());
    }

    /**
     * @covers \Hyper\AdsBundle\Entity\Advertisement::setPaidTo
     * @covers \Hyper\AdsBundle\Entity\Advertisement::getPaidTo
     * @covers \Hyper\AdsBundle\Entity\Advertisement::isActive
     */
    public function testIsActiveForPremiumAd()
    {
        $this->ad->setAnnouncementPaymentType(AnnouncementPaymentType::ANNOUNCEMENT_PAYMENT_TYPE_PREMIUM);
        $this->ad->setPaidTo(new \DateTime('now - 1 month'));
        $this->assertFalse($this->ad->isActive());
        $this->ad->setPaidTo(new \DateTime('now + 1 month'));
        $this->assertTrue($this->ad->isActive());
    }

    /**
     * @dataProvider validAnnouncementPaymentTypesProvider
     * @covers \Hyper\AdsBundle\Entity\Announcement::setAnnouncementPaymentType
     */
    public function testSetValidAnnouncementPaymentType($paymentType)
    {
        $this->ad->setAnnouncementPaymentType($paymentType);
        $this->assertAttributeEquals($paymentType, 'announcementPaymentType', $this->ad);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Given announcement payment type is invalid
     * @covers \Hyper\AdsBundle\Entity\Announcement::setAnnouncementPaymentType
     */
    public function testSetInvalidAnnouncementPaymentTypesProvider()
    {
        $this->ad->setAnnouncementPaymentType('invalid');
    }

    public function validAnnouncementPaymentTypesProvider()
    {
        return array(
            array(AnnouncementPaymentType::ANNOUNCEMENT_PAYMENT_TYPE_PREMIUM),
            array(AnnouncementPaymentType::ANNOUNCEMENT_PAYMENT_TYPE_STANDARD),
        );
    }
}
