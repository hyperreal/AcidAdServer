<?php

namespace Hyper\AdsBundle\Payment;

use Wikp\PaymentMtgoxBundle\Plugin\OrderInterface;

interface PaymentProcessorInterface
{
    function pay(OrderInterface $order);
}