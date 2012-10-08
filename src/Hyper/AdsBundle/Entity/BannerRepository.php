<?php

namespace Hyper\AdsBundle\Entity;

use Doctrine\ORM\EntityRepository;

class BannerRepository extends EntityRepository
{
    public function getAllActiveBannersInZone(Zone $zone)
    {
        $em = $this->getEntityManager();

        $query = $em->createQuery(
            'SELECT b, bzr
            FROM Hyper\AdsBundle\Entity\Banner b
            JOIN b.zones bzr
            JOIN b.campaign c
            WHERE c.expireDate > ?1 AND bzr.zone = ?2'
        );

        $query->setParameter(1, new \DateTime());
        $query->setParameter(2, $zone);

        return $query->getResult();
    }

    /**
     * @param Zone $zone
     *
     * @return Hyper\AdsBundle\Entity\Banner|null
     */
    public function getRandomBannerInZone(Zone $zone)
    {
        $allBannersInZone = $this->getAllActiveBannersInZone($zone);

        if (empty($allBannersInZone)) {
            return null;
        }

        $bannersArray = array();
        $zoneId = $zone->getId();

        foreach ($allBannersInZone as $banner) {
            /** @var $banner \Hyper\AdsBundle\Entity\Banner */
            /** @var $reference \Hyper\AdsBundle\Entity\BannerZoneReference */
            $reference = $banner->getReferenceInZone($zoneId);

            $probability = $reference->getProbability();
            for ($i=0; $i<$probability; $i++) {
                $bannersArray[] = $banner;
            }
        }

        $selectedBanner = array_rand($bannersArray, 1);

        return $bannersArray[$selectedBanner];
    }
}
