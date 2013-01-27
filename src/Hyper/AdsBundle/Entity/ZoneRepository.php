<?php

namespace Hyper\AdsBundle\Entity;

use Doctrine\ORM\EntityRepository;

class ZoneRepository extends EntityRepository
{
    /**
     * @param array $zonesIds
     * @return Zone[]
     */
    public function getZonesByIdsWithOrdersLoaded(array $zonesIds)
    {
        $query = $this->getEntityManager()->createQuery(
            'SELECT z, o
            FROM Hyper\AdsBundle\Entity\Zone z
            LEFT JOIN z.orders o
            WHERE z.id IN (?1)'
        );

        $query->setParameter(1, $zonesIds);

        return $query->getResult();
    }

    public function clearAllButSpecifiedZonesForBanner(Banner $banner, array $zoneIds)
    {
        if (empty($zoneIds)) {
            $query = $this->getEntityManager()->createQuery(
                'UPDATE Hyper\AdsBundle\Entity\BannerZoneReference bzr
                SET bzr.active = 0
                WHERE bzr.banner = ?1'
            );
        } else {
            $query = $this->getEntityManager()->createQuery(
                'UPDATE Hyper\AdsBundle\Entity\BannerZoneReference bzr
                SET bzr.active = 0
                WHERE bzr.banner = ?1 AND bzr.zone NOT IN (?2)'
            );
            $query->setParameter(2, $zoneIds);
        }

        $query->setParameter(1, $banner);
        $query->execute();
    }

    public function mapActiveZonesIdsToDailyPrice()
    {
        $mapping = array();
        $activeZones = $this->findBy(
            array(
                'active' => 1
            )
        );

        foreach ($activeZones as $zone) {
            /** @var $zone \Hyper\AdsBundle\Entity\Zone */
            $mapping[$zone->getId()] = $zone->getDailyPrice();
        }

        return $mapping;
    }
}
