<?php

namespace Hyper\AdsBundle\Payment\BitPay\Gateway;

use Hyper\AdsBundle\Payment\BitPay\BitPayPurchaseRequest;
use Omnipay\BitPay\Gateway as OmnipayGateway;

class BitPayGateway extends OmnipayGateway
{
    protected function createRequest($class, array $parameters)
    {
        return parent::createRequest(BitPayPurchaseRequest::class, $parameters);
    }

    public function purchase(array $parameters = array())
    {
        return $this->createRequest(BitPayPurchaseRequest::class, $parameters);
    }
}