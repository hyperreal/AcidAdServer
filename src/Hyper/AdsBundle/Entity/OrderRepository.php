<?php

namespace Hyper\AdsBundle\Entity;

use Doctrine\ORM\EntityRepository;
use Hyper\AdsBundle\Entity\Zone;
use Wikp\PaymentMtgoxBundle\Plugin\OrderRepositoryInterface;

class OrderRepository extends EntityRepository implements OrderRepositoryInterface
{
    public function getOrderById($id)
    {
        return $this->find($id);
    }

    /**
     * @param Zone $zone
     *
     * @return \Hyper\AdsBundle\Entity\Order[]
     */
    public function getOrdersForZone(Zone $zone)
    {
        $query = $this->getEntityManager()->createQuery(
            'SELECT o
            FROM Hyper\AdsBundle\Entity\Order o
            JOIN o.bannerZone bzr
            WHERE bzr.zone = ?1 AND o.paymentFrom >= ?2 AND o.status != ?3'
        );

        $query->setParameter(1, $zone);
        $query->setParameter(2, new \DateTime());
        $query->setParameter(3, Order::STATUS_CANCELLED);

        return $query->getResult();
    }
}
