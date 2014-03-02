<?php

namespace Hyper\AdsBundle\Payment;

use Omnipay\Common\AbstractGateway;

interface PaymentGatewayBuilderInterface
{
    /**
     * Implementation of this method should do everything that is needed to completely build a gateway object.
     * Example of this could be invoke setApiKey() method on one gateway type and setApiKey() and setApiSecret() on
     * another.
     *
     * Implementations of this interface should be registered as services tagged with hyper_ads.payment.gateway_builder
     *
     * @param AbstractGateway $gateway
     * @param array $parameters parameters provided in config.yml (section hyper_ads.payment_gateways).
     * @return AbstractGateway
     */
    function build(AbstractGateway $gateway, array $parameters);
} 