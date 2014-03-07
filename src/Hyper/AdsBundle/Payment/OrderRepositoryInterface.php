<?php

namespace Hyper\AdsBundle\Payment;

use Doctrine\Common\Persistence\ObjectRepository;

interface OrderRepositoryInterface extends ObjectRepository
{
    /**
     * @param $orderId
     * @return OrderInterface
     */
    public function getOrderById($orderId);
}
