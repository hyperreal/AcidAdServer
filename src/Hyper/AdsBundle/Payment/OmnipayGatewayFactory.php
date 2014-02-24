<?php

namespace Hyper\AdsBundle\Payment;

use Omnipay\Omnipay;

class OmnipayGatewayFactory implements PaymentGatewayFactoryInterface
{
    /** @var PaymentGatewayBuilderInterface[] */
    private $gatewayBuilders;

    function __construct()
    {
        $this->gatewayBuilders = array();
    }

    public function addGatewayBuilder(PaymentGatewayBuilderInterface $builder, $gatewayName)
    {
        $this->gatewayBuilders[$gatewayName] = $builder;
    }

    public function createGateway(array $parameters)
    {
        /** @var $gateway \Omnipay\Common\AbstractGateway */
        $gateway = Omnipay::getFactory()->create($parameters['name']);
        $this->gatewayBuilders[$parameters['name']]->build($gateway, $parameters);

        return $gateway;
    }
}