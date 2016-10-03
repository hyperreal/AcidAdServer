<?php

namespace Hyper\AdsBundle\Payment\Electrum;

use JMS\Payment\CoreBundle\Model\FinancialTransactionInterface;
use JMS\Payment\CoreBundle\Plugin\AbstractPlugin;
use JMS\Payment\CoreBundle\Plugin\PluginInterface;

class OmnipayElectrumPaymentPlugin extends AbstractPlugin
{

  const SYSTEM_NAME = 'omnipay_electrum';
  
  /**
   * Whether this plugin can process payments for the given payment system.
   *
   * A plugin may support multiple payment systems. In these cases, the requested
   * payment system for a specific transaction  can be determined by looking at
   * the PaymentInstruction which will always be accessible either directly, or
   * indirectly.
   *
   * @param string $paymentSystemName
   * @return boolean
   */
  public function processes($paymentSystemName) {
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