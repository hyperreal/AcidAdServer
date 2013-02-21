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

    /**
     * @var \Doctrine\ORM\EntityManager
     */
    private $entityManager;

    /**
     * @var \Hyper\AdsBundle\Entity\Zone
     */
    private $zone;

    /**
     * @var \Hyper\AdsBundle\Entity\Banner[]
     */
    private $banners = array();

    /**
     * @var array
     */
    private $probabilities = array();

    /**
     * @var array
     */
    private $currentReferencesIds = array();

    private $adminFix;

    /**
     * @var array
     */
    private $newReferencesIds = array();

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

    public function setAdminFix($fix)
    {
        $this->adminFix = !!$fix;
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

    /**
     * @throws \InvalidArgumentException
     */
    public function updateReferences()
    {
        $this->filterUnusedProbabilities();
        $this->validateParameters();
        $this->updateReferencesInTransaction();
    }

    private function updateReferencesInTransaction()
    {
        $this->entityManager->beginTransaction();
        $this->retrieveCurrentReferencesIds();
        $this->updateBannersProbabilities();
        $this->removeUnusedReferences();
        $this->entityManager->commit();
    }

    private function removeUnusedReferences()
    {
        $toDeleteRefsIds = array_diff($this->currentReferencesIds, $this->newReferencesIds);

        if (empty($toDeleteRefsIds)) {
            return;
        }

        $query = $this->entityManager->createQuery(
            'UPDATE Hyper\AdsBundle\Entity\BannerZoneReference bzr
             SET bzr.active = 0, bzr.fixedByAdmin = 0
             WHERE bzr.id IN (?1)'
        );

        $query->setParameter(1, $toDeleteRefsIds);
        $query->execute(); //don't need to call $em->clear() because we perform redirect
    }

    private function updateBannersProbabilities()
    {
        foreach ($this->banners as $banner) {
            $ref = $this->getReference($banner);
            $ref->setProbability($this->probabilities[$banner->getId()]);
            $ref->setActive(true);
            $ref->setFixedByAdmin(true);
            $this->persistReference($ref);
            $this->newReferencesIds[] = $ref->getId();
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

    private function filterUnusedProbabilities()
    {
        $probabilities = array();

        foreach ($this->banners as $banner) {
            if (isset($this->probabilities[$banner->getId()])) {
                $probabilities[$banner->getId()] = $this->probabilities[$banner->getId()];
            }
        }

        $this->probabilities = $probabilities;
    }

    private function validateParameters()
    {
        if (empty($this->zone)) {
            throw new \InvalidArgumentException(self::ERROR_ZONE_NOT_SET);
        }

        if (count($this->banners) != count($this->probabilities)) {
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

    private function retrieveCurrentReferencesIds()
    {
        $currentReferences = $this->getBannerReferencesFromZone();
        $this->currentReferencesIds = array();

        foreach ($currentReferences as $reference) {
            $this->currentReferencesIds[] = $reference->getId();
        }
    }

    /**
     * @return \Hyper\AdsBundle\Entity\BannerZoneReference[]
     */
    private function getBannerReferencesFromZone()
    {
        /** @var $repository \Hyper\AdsBundle\Entity\BannerZoneReferenceRepository */
        $repository = $this->entityManager->getRepository('HyperAdsBundle:BannerZoneReference');

        return $repository->getBannerReferencesByZone($this->zone);
    }
}

