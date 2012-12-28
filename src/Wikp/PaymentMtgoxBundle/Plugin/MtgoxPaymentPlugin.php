<?php

namespace Wikp\PaymentMtgoxBundle\Plugin;

use JMS\Payment\CoreBundle\Plugin\AbstractPlugin;
use JMS\Payment\CoreBundle\Model\FinancialTransactionInterface;
use JMS\Payment\CoreBundle\Util\Number;
use Wikp\PaymentMtgoxBundle\Mtgox\Client;

class MtgoxPaymentPlugin extends AbstractPlugin
{
    const SYSTEM_NAME = 'mtgox_instant_payment';
    const CURRENCY_NAME = 'BTC';

    private $client;

    private $returnUrl;
    private $cancelUrl;

    public function __construct($returnUrl, $cancelUrl, Client $client)
    {
        $this->returnUrl = $returnUrl;
        $this->cancelUrl = $cancelUrl;
        $this->client = $client;
    }

    public function approve(FinancialTransactionInterface $transaction, $retry)
    {

    }

    public function approveAndDeposit(FinancialTransactionInterface $transaction, $retry)
    {

    }

    public function processes($paymentSystemName)
    {
        return self::SYSTEM_NAME == $paymentSystemName;
    }
}
