<?php

namespace Hyper\AdsBundle\Payment;

use Omnipay\Omnipay;

class OmnipayGatewayFactory implements PaymentGatewayFactoryInterface
{
    /** @var PaymentGatewayBuilderInterface[] */
    private $gatewayBuilders;

    /** @var \Omnipay\Common\AbstractGateway[] */
    private $gateways;

    public function __construct()
    {
        $this->gatewayBuilders = array();
        $this->gateways = array();
    }

    public function addGatewayBuilder(PaymentGatewayBuilderInterface $builder, $gatewayName)
    {
        $this->gatewayBuilders[$gatewayName] = $builder;
    }

    public function createGateway(array $parameters)
    {
        if (array_key_exists($parameters['name'], $this->gateways)) {
            return $this->gateways[$parameters['name']];
        }

        /** @var $gateway \Omnipay\Common\AbstractGateway */
        $gateway = Omnipay::getFactory()->create($parameters['name']);
        $this->gatewayBuilders[$parameters['name']]->build($gateway, $parameters);

        $this->gateways[$parameters['name']] = $gateway;

        return $gateway;
    }
}