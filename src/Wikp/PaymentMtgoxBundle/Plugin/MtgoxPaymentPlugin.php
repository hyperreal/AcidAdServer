<?php

namespace Wikp\PaymentMtgoxBundle\Plugin;

use Doctrine\ORM\EntityManager;
use JMS\Payment\CoreBundle\Plugin\AbstractPlugin;
use JMS\Payment\CoreBundle\Model\FinancialTransactionInterface;
use JMS\Payment\CoreBundle\Util\Number;
use Wikp\PaymentMtgoxBundle\Mtgox\Client;
use Wikp\PaymentMtgoxBundle\Mtgox\RequestType\MtgoxTransactionUrlRequest;

class MtgoxPaymentPlugin extends AbstractPlugin
{
    const SYSTEM_NAME = 'mtgox_instant_payment';
    const CURRENCY_NAME = 'BTC';

    /** @var \Wikp\PaymentMtgoxBundle\Mtgox\Client */
    private $client;
    /** @var \Doctrine\ORM\EntityManager */
    private $entityManager;

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
        $transaction->setState(FinancialTransactionInterface::STATE_SUCCESS);
    }

    public function approveAndDeposit(FinancialTransactionInterface $transaction, $retry)
    {

    }

    public function processes($paymentSystemName)
    {
        return self::SYSTEM_NAME == $paymentSystemName;
    }

    public function getMtgoxTransactionUrl(MtgoxTransactionUrlRequest $request)
    {
        $response = $this->client->rawRequest($request->asRequest());
        return $response->get('payment_url');
    }
}
