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

    public function updateReferences(Zone $zone, array $banners, array $probabilities)
    {
        $em = $this->getEntityManager();

        $currentReferences = $this->getBannerReferencesByZone($zone);
        $currentReferencesIds = $newReferencesIds = array();

        foreach ($currentReferences as $reference) {
            $currentReferencesIds[] = $reference->getId();
        }

        $em->beginTransaction();

        foreach ($banners as $banner) {

            if (!isset($probabilities[$banner->getId()])) {
                continue;
            }

            $ref = $banner->getReferenceInZone($zone->getId());

            if (null === $ref) {
                $ref = new BannerZoneReference();
                $ref->setViews(0);
                $ref->setClicks(0);
                $ref->setBanner($banner);
                $ref->setZone($zone);
            }

            $ref->setProbability($probabilities[$banner->getId()]);

            $em->persist($ref);
            $em->flush();

            $newReferencesIds[] = $ref->getId();
        }

        $toDeleteRefsIds = array_diff($currentReferencesIds, $newReferencesIds);

        if (empty($toDeleteRefsIds)) {
            $em->commit();
            return;
        }

        $query = $em->createQuery(
            'UPDATE Hyper\AdsBundle\Entity\BannerZoneReference bzr
             SET bzr.active = 0
             WHERE bzr.id IN (?1)'
        );
        $query->setParameter(1, $toDeleteRefsIds);
        $query->execute(); //don't need to call $em->clear() because we perform redirect

        $em->commit();
    }

}
