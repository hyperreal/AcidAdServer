<?php

namespace Hyper\AdsBundle\Tests\Helper;

use Hyper\AdsBundle\Helper\ReferencesUpdater;
use Hyper\AdsBundle\Entity\Banner;
use Hyper\AdsBundle\Entity\Zone;

class ReferencesUpdaterTest extends \PHPUnit_Framework_TestCase
{
    const FAKE_BANNER_ID = 99;
    const MOCK_PROBABILITY_1 = 42;
    const MOCK_PROBABILITY_2 = 12;
    const MOCK_ZONE_ID = 997;
    const MOCK_BANNER_ID_1 = 1;
    const MOCK_BANNER_ID_2 = 2;
    /**
     * @var \Hyper\AdsBundle\Helper\ReferencesUpdater
     */
    protected $updater;

    /**
     * @var \Hyper\AdsBundle\Entity\Banner;
     */
    protected $banner1;

    /**
     * @var \Hyper\AdsBundle\Entity\Banner;
     */
    protected $banner2;

    public function setUp()
    {
        $this->updater = new ReferencesUpdater($this->getEntityManagerMock());
        $this->banner1 = new Banner();
        $this->banner1->setId(self::MOCK_BANNER_ID_1);
        $this->banner2 = new Banner();
        $this->banner2->setId(self::MOCK_BANNER_ID_2);
    }

    /**
     * @expectedException PHPUnit_Framework_Error
     * @dataProvider nonHigherOrderObjectProvider
     */
    public function testClassCreationWithInvalidParam($em)
    {
        new ReferencesUpdater($em);
    }

    public function testSetZone()
    {
        $zone = new Zone();
        $zone->setId(self::MOCK_ZONE_ID);
        $this->updater->setZone($zone);
        $this->assertAttributeEquals($zone, 'zone', $this->updater);
    }

    public function testSetOnlyBanners()
    {
        $arrayOfBanners = array($this->banner1, $this->banner2);
        $this->updater->setBanners($arrayOfBanners);

        $this->assertAttributeEquals($arrayOfBanners, 'banners', $this->updater);
    }

    public function testSetEmptyBannersArray()
    {
        $this->updater->setBanners(array());
        $this->assertAttributeEmpty('banners', $this->updater);
    }

    /**
     * @dataProvider nonHigherOrderObjectProvider
     */
    public function testSetArrayOfNotOnlyBanners($somethingElse)
    {
        $arrayOfNotOnlyBanners = array($this->banner1, $somethingElse);
        $this->updater->setBanners($arrayOfNotOnlyBanners);
        $this->assertAttributeEquals(array($this->banner1), 'banners', $this->updater);
    }

    public function testSetProbabilities()
    {
        $probabilities = array(self::MOCK_PROBABILITY_1, self::MOCK_PROBABILITY_2);
        $this->updater->setProbabilities($probabilities);
        $this->assertAttributeEquals($probabilities, 'probabilities', $this->updater);
    }

    /**
     * @dataProvider nonArrayProvider
     * @expectedException PHPUnit_Framework_Error
     */
    public function testSetInvalidProbabilities($probabilities)
    {
        $this->updater->setProbabilities($probabilities);
    }

    public function testRaisingExceptionZoneNotSet()
    {
        $probabilities = array(self::MOCK_PROBABILITY_1, self::MOCK_PROBABILITY_2);
        $this->updater->setProbabilities($probabilities);
        $this->setExpectedException('InvalidArgumentException', ReferencesUpdater::ERROR_ZONE_NOT_SET);
        $this->updater->updateReferences();
    }

    public function testRaisingExceptionProbabilitiesBannersInequality()
    {
        $this->updater->setZone(new Zone());
        $this->updater->setBanners(array($this->banner1, $this->banner2));
        $this->updater->setProbabilities(
            array(
                $this->banner1->getId() => self::MOCK_PROBABILITY_1,
                self::FAKE_BANNER_ID => self::MOCK_PROBABILITY_2
            )
        );
        $this->setExpectedException('InvalidArgumentException', ReferencesUpdater::ERROR_PROBABILITIES_BANNER_COUNT);
        $this->updater->updateReferences();
    }

    public function nonHigherOrderObjectProvider()
    {
        return array(
            array(new \stdClass()),
            array('string'),
            array(17),
            array(fopen('php://stdin', 'r')),
            array(array()),
            array(null),
            array(false),
        );
    }

    public function nonArrayProvider()
    {
        $objectProvider = $this->nonHigherOrderObjectProvider();
        unset($objectProvider[4]);
        return $objectProvider;
    }

    protected function getEntityManagerMock()
    {
        $em = $this->getMock(
            'Doctrine\ORM\EntityManager',
            array('persist', 'flush', 'beginTransaction', 'commit', 'rollback', 'getRepository'),
            array(),
            '',
            false
        );

        $em->expects($this->any())
            ->method('persist')
            ->will($this->returnValue(null));
        $em->expects($this->any())
            ->method('flush')
            ->will($this->returnValue(null));
        $em->expects($this->any())
            ->method('beginTransaction')
            ->will($this->returnValue(null));
        $em->expects($this->any())
            ->method('commit')
            ->will($this->returnValue(null));
        $em->expects($this->any())
            ->method('rollback')
            ->will($this->returnValue(null));

        $repo = $this->getBannerZoneReferenceRepositoryMock(array(), new Zone());

        $em->expects($this->any())
            ->method('getRepository')
            ->with('HyperAdsBundle:BannerZoneReference')
            ->will($this->returnValue($repo));

        return $em;
    }

    protected function getBannerZoneReferenceRepositoryMock(array $references, Zone $zone)
    {
        $repoMock = $this->getMock(
            'Hyper\AdsBundle\Entity\BannerZoneReferenceRepository',
            array('getBannerReferencesByZone'),
            array(),
            '',
            false
        );

        $repoMock->expects($this->any())
            ->method('getBannerReferencesByZone')
            ->with($zone)
            ->will($this->returnValue($references));

        return $repoMock;
    }
}
