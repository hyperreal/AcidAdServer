<?php

namespace Hyper\AdsBundle\Payment;

interface ParamsProviderInterface
{
    /**
     * @param OrderInterface $order
     * @return array
     */
    public function getParametersFromOrder(OrderInterface $order);
} 