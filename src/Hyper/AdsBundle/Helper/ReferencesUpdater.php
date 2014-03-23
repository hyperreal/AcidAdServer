<?php

namespace Hyper\AdsBundle\Helper;

use Hyper\AdsBundle\Entity\Banner;
use Hyper\AdsBundle\Entity\Zone;
use Hyper\AdsBundle\Entity\BannerZoneReference;
use Doctrine\ORM\EntityManager;

class ReferencesUpdater
{
    const ERROR_ZONE_NOT_SET = 'Zone is not set';
    const ERROR_PROBABILITIES_BANNER_COUNT = 'Number of banners is not equal with number of probabilities';

    /** @var \Doctrine\ORM\EntityManager */
    private $entityManager;

    /** @var \Hyper\AdsBundle\Entity\Zone */
    private $zone;

    /** @var \Hyper\AdsBundle\Entity\Banner[] */
    private $banners = array();

    /** @var array */
    private $probabilities = array();

    /** @var array */
    private $fixedByAdminSpecification = array();

    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @param Zone $zone
     */
    public function setZone(Zone $zone)
    {
        $this->zone = $zone;
    }

    /**
     * @param \Hyper\AdsBundle\Entity\Banner[] $banners
     */
    public function setBanners(array $banners = array())
    {
        $this->banners = array_filter(
            $banners,
            function ($banner) {
                return $banner instanceof Banner;
            }
        );
    }

    public function setProbabilities(array $probabilities = array())
    {
        $this->probabilities = $probabilities;
    }

    public function setFixedByAdminSpecification(array $spec = array())
    {
        $this->fixedByAdminSpecification = $spec;
    }

    /**
     * @throws \InvalidArgumentException
     */
    public function updateReferences()
    {
        $this->validateParameters();
        $this->updateReferencesInTransaction();
    }

    private function updateReferencesInTransaction()
    {
        $this->entityManager->beginTransaction();
        $this->updateBanners();
        $this->entityManager->commit();
    }

    private function updateBanners()
    {
        foreach ($this->banners as $banner) {
            $ref = $this->getReference($banner);
            $ref->setProbability($this->probabilities[$banner->getId()]);
            $ref->setFixedByAdmin(intval($this->fixedByAdminSpecification[$banner->getId()]));
            $this->persistReference($ref);
        }
    }

    private function persistReference($ref)
    {
        $this->entityManager->persist($ref);
        $this->entityManager->flush();
    }

    private function getReference($banner)
    {
        $ref = $banner->getReferenceInZone($this->zone->getId());

        if (null === $ref) {
            $ref = $this->createNewReference($banner);
        }

        return $ref;
    }

    private function validateParameters()
    {
        if (empty($this->zone)) {
            throw new \InvalidArgumentException(self::ERROR_ZONE_NOT_SET);
        }

        if (count($this->banners) != count($this->probabilities)
            || count($this->banners) != count($this->fixedByAdminSpecification)
        ) {
            throw new \InvalidArgumentException(self::ERROR_PROBABILITIES_BANNER_COUNT);
        }
    }

    /**
     * @param \Hyper\AdsBundle\Entity\Banner $banner
     *
     * @return \Hyper\AdsBundle\Entity\BannerZoneReference
     */
    private function createNewReference(Banner $banner)
    {
        $ref = new BannerZoneReference();
        $ref->setBanner($banner);
        $ref->setZone($this->zone);

        return $ref;
    }
}
