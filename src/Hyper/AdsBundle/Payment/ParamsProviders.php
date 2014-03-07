<?php

namespace Hyper\AdsBundle\Payment;

class ParamsProviders implements ParamsProviderInterface
{
    /** @var \Hyper\AdsBundle\Payment\ParamsProviderInterface[] */
    private $providers = array();

    public function addProvider(ParamsProviderInterface $paramsProvider, $systemName)
    {
        $this->providers[$systemName] = $paramsProvider;
    }

    /**
     * @param OrderInterface $order
     * @return array
     */
    public function getParametersFromOrder(OrderInterface $order)
    {
        return $this->providers[$order->getPaymentInstruction()->getPaymentSystemName()]
            ->getParametersFromOrder($order);
    }
}