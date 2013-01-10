<?php

namespace Hyper\AdsBundle\Entity;

use Doctrine\ORM\EntityRepository;

class AnnouncementRepository extends EntityRepository
{
    /**
     * @param $id
     * @return \Hyper\AdsBundle\Entity\Banner
     */
    public function getBannerWithDependenciesById($id)
    {
        $em = $this->getEntityManager();

        $query = $em->createQuery(
            'SELECT b, o, pi, z
             FROM Hyper\AdsBundle\Entity\Banner b
             LEFT JOIN b.orders o
             LEFT JOIN o.zone AS z
             LEFT JOIN o.paymentInstruction pi
             WHERE b.id = ?1'
        );

        $query->setParameter(1, $id);
        $query->setMaxResults(1);

        return $query->getSingleResult();
    }

    public function getBannersWithDependenciesByAdvertiser(Advertiser $advertiser)
    {
        $query = $this->getEntityManager()->createQuery(
            'SELECT b, o, z
            FROM Hyper\AdsBundle\Entity\Banner b
            LEFT JOIN b.orders o
            LEFT JOIN o.zone AS z
            WHERE b.advertiser = ?1'
        );

        $query->setParameter(1, $advertiser);

        return $query->getResult();
    }

    public function getAllActiveBannersInZone(Zone $zone)
    {
        $em = $this->getEntityManager();

        $query = $em->createQuery(
            'SELECT b, bzr
             FROM Hyper\AdsBundle\Entity\Banner b
             JOIN b.zones bzr
             WHERE bzr.zone = ?1'
        );

        $query->setParameter(1, $zone);

        return $query->getResult();
    }

    /**
     * @return \Hyper\AdsBundle\Entity\Banner[]
     */
    public function getAllActiveBanners()
    {
        $em = $this->getEntityManager();

        $query = $em->createQuery(
            'SELECT b, bzr
             FROM Hyper\AdsBundle\Entity\Banner b
             LEFT JOIN b.zones bzr'
        );

        return $query->getResult();
    }

    /**
     * @param Zone $zone
     *
     * @return \Hyper\AdsBundle\Entity\Banner|null
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

    /**
     * @param     $bannerId
     * @param     $zoneId
     *
     * @return \Hyper\AdsBundle\Entity\BannerZoneReference
     */
    public function getBannerReference($bannerId, $zoneId)
    {
        $em = $this->getEntityManager();

        $query = $em->createQuery(
            'SELECT bzr, b
             FROM Hyper\AdsBundle\Entity\BannerZoneReference bzr
             JOIN bzr.banner b
             JOIN bzr.zone z
             WHERE b.id = ?1 AND z.id = ?2'
        );

        $query->setParameter(1, (int)$bannerId);
        $query->setParameter(2, (int)$zoneId);

        return $query->getOneOrNullResult();
    }

    /**
     * @param Banner $banner
     * @return Zone[]
     */
    public function getPossibleZonesForBanner(Banner $banner)
    {
        $query = $this->getEntityManager()->createQuery(
            'SELECT z, b, bzr, p
            FROM Hyper\AdsBundle\Entity\Zone z
            LEFT JOIN z.banners bzr
            LEFT JOIN bzr.banner b
            JOIN z.page p
            WHERE z.maxWidth >= ?1 AND z.maxHeight >= ?2'
        );

        $query->setParameter(1, $banner->getWidth());
        $query->setParameter(2, $banner->getHeight());

        return $query->getResult();
    }

}
