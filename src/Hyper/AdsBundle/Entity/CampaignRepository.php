<?php

namespace Hyper\AdsBundle\Entity;

use Doctrine\ORM\EntityRepository;

class CampaignRepository extends EntityRepository
{
    const EXPIRING_DAYS = 5;

    /**
     * @return \Hyper\AdsBundle\Entity\Campaign[]
     */
    public function getExpiringCampaigns()
    {
        $query = $this->getEntityManager()->createQuery(
            'SELECT c, a
            FROM Hyper\AdsBundle\Entity\Campaign c
            JOIN c.advertiser a
            WHERE c.expireDate > ?1 AND c.expireDate < ?2'
        );

        $query->setParameter(1, new \DateTime());
        $query->setParameter(2, new \DateTime('now + ' . self::EXPIRING_DAYS . ' days'));

        return $query->getResult();
    }
}
