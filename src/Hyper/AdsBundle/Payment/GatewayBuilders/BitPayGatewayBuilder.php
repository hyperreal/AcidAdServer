<?php

namespace Hyper\AdsBundle\Payment\GatewayBuilders;

use Hyper\AdsBundle\Payment\PaymentGatewayBuilderInterface;
use Omnipay\BitPay\Gateway;
use Omnipay\Common\AbstractGateway;

class BitPayGatewayBuilder implements PaymentGatewayBuilderInterface
{
    public function build(AbstractGateway $gateway, array $parameters)
    {
        if (!($gateway instanceof Gateway)) {
            throw new \InvalidArgumentException(
                'BitPayGatewayBuilder is responsible for set-up Omnipay\Bitpay\Gateway classes only'
            );
        }

        $gateway->setApiKey($parameters['api_key']);

        return $gateway;
    }
}