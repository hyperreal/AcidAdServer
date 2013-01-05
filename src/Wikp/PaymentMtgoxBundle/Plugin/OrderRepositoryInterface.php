<?php

namespace Wikp\PaymentMtgoxBundle\Plugin;

use Doctrine\Common\Persistence\ObjectRepository;

interface OrderRepositoryInterface extends ObjectRepository
{
    /**
     * @param $orderId
     * @return OrderInterface
     */
    public function getOrderById($orderId);
}
