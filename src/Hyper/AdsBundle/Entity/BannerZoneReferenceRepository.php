<?php

namespace Hyper\AdsBundle\Entity;

use Doctrine\ORM\EntityRepository;

class BannerZoneReferenceRepository extends EntityRepository
{
    /**
     * @param Zone $zone
     *
     * @return \Hyper\AdsBundle\Entity\BannerZoneReference[]
     */
    public function getBannerReferencesByZone(Zone $zone)
    {
        $query = $this->getEntityManager()->createQuery(
            'SELECT bzr, b
             FROM Hyper\AdsBundle\Entity\BannerZoneReference bzr
             JOIN bzr.banner b
             WHERE bzr.zone = ?1'
        );

        $query->setParameter(1, $zone);

        return $query->getResult();
    }
}
