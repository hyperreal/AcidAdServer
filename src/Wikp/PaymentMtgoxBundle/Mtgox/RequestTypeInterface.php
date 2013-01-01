<?php

namespace Wikp\PaymentMtgoxBundle\Mtgox;

interface RequestTypeInterface
{
    /**
     * @return Request
     */
    public function asRequest();
}
