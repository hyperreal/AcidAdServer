<?php

namespace Hyper\AdsBundle\Payment\Util;

use Hyper\AdsBundle\Payment\OrderInterface;

interface OrderHashGeneratorInterface
{
    public function hashOrder(OrderInterface $order);
} 