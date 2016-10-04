<?php

namespace Hyper\AdsBundle\Payment\Electrum\Gateway;

use Omnipay\Common\AbstractGateway;
use Hyper\AdsBundle\Payment\Electrum\Gateway\Message\PurchaseRequest;

/**
 * Skeleton Gateway
 */
class ElectrumGateway extends AbstractGateway
{
    public function getName()
    {
        return 'Electrum';
    }

    public function getDefaultParameters()
    {
        return array(
            'key' => '',
            'testMode' => false,
        );
    }

    public function getKey()
    {
        return $this->getParameter('key');
    }

    public function setKey($value)
    {
        return $this->setParameter('key', $value);
    }

    public function purchase(array $parameters = array()) {
        $this->httpRequest->setFormat('json', 'application/json');
        return $this->createRequest('\Hyper\AdsBundle\Payment\Electrum\Gateway\Message\PurchaseRequest', $parameters);
    }
}
