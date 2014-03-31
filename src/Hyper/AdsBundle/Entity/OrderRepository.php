<?php

namespace Hyper\AdsBundle\Entity;

use Doctrine\ORM\EntityRepository;
use Hyper\AdsBundle\Payment\OrderRepositoryInterface;

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
            WHERE bzr.zone = ?1 AND (o.status = ?2 OR o.status = ?3)'
        );

        $query->setParameter(1, $zone);
        $query->setParameter(2, Order::STATUS_FINISHED);
        $query->setParameter(3, Order::STATUS_NEW);

        return $query->getResult();
    }

    public function removeExpiredOrders($time)
    {
        $query = $this->getEntityManager()->createQuery(
            'UPDATE Hyper\AdsBundle\Entity\Order o
            SET o.status = ?1
            WHERE o.status = 0 AND o.creationDate < ?2'
        );
        $query->setParameter(1, Order::STATUS_NOT_PAID);
        $query->setParameter(2, new \DateTime("now - $time minutes"));

        return $query->execute();
    }
}
