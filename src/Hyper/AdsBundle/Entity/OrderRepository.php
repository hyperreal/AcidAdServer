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
            WHERE bzr.zone = ?1 AND o.status = ?2'
        );

        $query->setParameter(1, $zone);
        $query->setParameter(2, Order::STATUS_FINISHED);

        return $query->getResult();
    }

    public function removeExpiredOrders($time)
    {
        $query = $this->getEntityManager()->createQuery(
            'DELETE FROM Hyper\AdsBundle\Entity\Order o
            WHERE o.status = 0 AND o.creationDate < ?1'
        );
        $query->setParameter(1, new \DateTime("now - $time minutes"));

        return $query->execute();
    }
}
