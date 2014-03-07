<?php

namespace Hyper\AdsBundle\Payment;

use JMS\Payment\CoreBundle\Model\PaymentInstructionInterface;

interface OrderInterface
{
    /**
     * @return \JMS\Payment\CoreBundle\Model\PaymentInstructionInterface
     */
    public function getPaymentInstruction();
    public function setPaymentInstruction(PaymentInstructionInterface $paymentInstruction);
    public function cancel();
    public function approve();
    public function getId();
    public function getOrderNumber();
}
