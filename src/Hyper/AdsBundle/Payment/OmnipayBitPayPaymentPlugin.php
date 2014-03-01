<?php

namespace Hyper\AdsBundle\Payment;

use JMS\Payment\CoreBundle\Model\FinancialTransactionInterface;
use JMS\Payment\CoreBundle\Plugin\AbstractPlugin;
use JMS\Payment\CoreBundle\Plugin\PluginInterface;

class OmnipayBitPayPaymentPlugin extends AbstractPlugin
{
    const SYSTEM_NAME = 'omnipay_bitpay';

    function processes($paymentSystemName)
    {
        return self::SYSTEM_NAME == $paymentSystemName;
    }

    public function approve(FinancialTransactionInterface $transaction, $retry)
    {
        $transaction->setState(FinancialTransactionInterface::STATE_SUCCESS);
    }

    public function approveAndDeposit(FinancialTransactionInterface $transaction, $retry)
    {
        /** @var $payment \Jms\Payment\CoreBundle\Model\PaymentInterface */
        $payment = $transaction->getPayment();
        $transaction->setProcessedAmount($payment->getTargetAmount());
        $transaction->setState(FinancialTransactionInterface::STATE_SUCCESS);
        $transaction->setResponseCode(PluginInterface::RESPONSE_CODE_SUCCESS);
        $transaction->setReasonCode(PluginInterface::REASON_CODE_SUCCESS);
    }
}