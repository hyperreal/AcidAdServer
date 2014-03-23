<?php

namespace Hyper\AdsBundle\Payment;

interface PaymentGatewayFactoryInterface
{
    public function createGateway(array $parameters);
    public function addGatewayBuilder(PaymentGatewayBuilderInterface $builder, $gatewayName);
}