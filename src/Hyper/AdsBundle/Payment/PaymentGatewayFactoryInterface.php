<?php

namespace Hyper\AdsBundle\Payment;

interface PaymentGatewayFactoryInterface
{
    function createGateway(array $parameters);
    function addGatewayBuilder(PaymentGatewayBuilderInterface $builder, $gatewayName);
}