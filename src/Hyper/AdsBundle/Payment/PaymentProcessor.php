<?php

namespace Hyper\AdsBundle\Payment;

use Wikp\PaymentMtgoxBundle\Plugin\OrderInterface;

class PaymentProcessor implements PaymentProcessorInterface
{
    /** @var \Omnipay\Common\AbstractGateway */
    private $paymentGateway;

    public function pay(OrderInterface $order)
    {

    }
}