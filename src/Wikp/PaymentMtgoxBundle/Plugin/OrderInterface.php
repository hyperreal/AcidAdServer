<?php

namespace Wikp\PaymentMtgoxBundle\Plugin;

interface OrderInterface
{
    /**
     * @return \JMS\Payment\CoreBundle\Model\PaymentInstructionInterface
     */
    public function getPaymentInstruction();
    public function cancel();
    public function approve();
}
