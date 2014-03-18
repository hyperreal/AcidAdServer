<?php

namespace Hyper\AdsBundle\Payment\BitPay;

use Omnipay\BitPay\Gateway as OmnipayGateway;

class Gateway extends OmnipayGateway
{
    protected function createRequest($class, array $parameters)
    {
        return parent::createRequest('Hyper\AdsBundle\Payment\BitPay\InvoiceRequest', $parameters);
    }

    public function purchase(array $parameters = array())
    {
        return $this->createRequest('Hyper\AdsBundle\Payment\BitPay\InvoiceRequest', $parameters);
    }
}