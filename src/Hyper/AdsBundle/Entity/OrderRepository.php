<?php

namespace Hyper\AdsBundle\Entity;

use Doctrine\ORM\EntityRepository;
use Wikp\PaymentMtgoxBundle\Plugin\OrderRepositoryInterface;

class OrderRepository extends EntityRepository implements OrderRepositoryInterface
{
    public function getOrderById($id)
    {
        return $this->find($id);
    }
}
