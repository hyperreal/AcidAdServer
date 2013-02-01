<?php

namespace Wikp\PaymentMtgoxBundle\Plugin;

use JMS\Payment\CoreBundle\Plugin\AbstractPlugin;
use JMS\Payment\CoreBundle\Model\FinancialTransactionInterface;
use JMS\Payment\CoreBundle\Plugin\PluginInterface;
use JMS\Payment\CoreBundle\Util\Number;
use Wikp\PaymentMtgoxBundle\Mtgox\Client;
use Wikp\PaymentMtgoxBundle\Mtgox\RequestType\MtgoxTransactionUrlRequest;
use Wikp\PaymentMtgoxBundle\Form\MtgoxIpnType;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class MtgoxPaymentPlugin extends AbstractPlugin
{
    const SYSTEM_NAME = 'mtgox_instant_payment';
    const CURRENCY_NAME = 'BTC';

    /** @var \Wikp\PaymentMtgoxBundle\Mtgox\Client */
    private $client;

    /**
     * @var \Symfony\Component\Form\FormFactory
     */
    private $formFactory;

    /** @var \Symfony\Component\HttpFoundation\Request */
    private $request;

    private $returnUrl;
    private $cancelUrl;

    public function __construct($returnUrl, $cancelUrl, Client $client, FormFactory $formFactory, Request $request)
    {
        $this->returnUrl = $returnUrl;
        $this->cancelUrl = $cancelUrl;
        $this->client = $client;
        $this->formFactory = $formFactory;
        $this->request = $request;
    }

    public function approve(FinancialTransactionInterface $transaction, $retry)
    {
        $transaction->setState(FinancialTransactionInterface::STATE_SUCCESS);
    }

    public function approveAndDeposit(FinancialTransactionInterface $transaction, $retry)
    {
        /*try {
            $wholeRequest = $this->prepareRequestArray($this->request);
        } catch (AccessDeniedException $ex) {
            $transaction->setResponseCode(PluginInterface::RESPONSE_CODE_PENDING);
            $transaction->setReasonCode(PluginInterface::REASON_CODE_INVALID);
            $transaction->setState(FinancialTransactionInterface::STATE_FAILED);
            return;
        }

        $form = $this->formFactory->create('wikp_mtgox_ipn');
        $form->bind($wholeRequest);

        if (!$form->isValid()) {
            //todo throw exception?
            $transaction->setResponseCode(PluginInterface::RESPONSE_CODE_PENDING);
            $transaction->setReasonCode(PluginInterface::REASON_CODE_INVALID);
            $transaction->setState(FinancialTransactionInterface::STATE_FAILED);
            return;
        }

        if ($form->get('status')->getData() === MtgoxIpnType::STATUS_CANCELLED) {
            $transaction->setResponseCode(PluginInterface::RESPONSE_CODE_PENDING);
            $transaction->setReasonCode(PluginInterface::REASON_CODE_INVALID);
            $transaction->setState(FinancialTransactionInterface::STATE_CANCELED);
            return;
        }
        */

        /** @var $payment \Jms\Payment\CoreBundle\Model\PaymentInterface */
        $payment = $transaction->getPayment();
        $transaction->setProcessedAmount($payment->getTargetAmount());
        $transaction->setState(FinancialTransactionInterface::STATE_SUCCESS);
        $transaction->setResponseCode(PluginInterface::RESPONSE_CODE_SUCCESS);
        $transaction->setReasonCode(PluginInterface::REASON_CODE_SUCCESS);
    }

    private function prepareRequestArray(Request $request)
    {
        if (!$request->server->has('HTTP_REST_SIGN')) {
            throw new AccessDeniedException("You didn't provide Rest-Sign header");
        }

        $wholeRequest = $request->request->all();
        unset($wholeRequest['ipnRequestObject']);

        $wholeRequest['ipnRequestObject'] = new IpnRequest(
            file_get_contents('php://input'),
            $request->server->get('HTTP_REST_SIGN')
        );

        return $wholeRequest;
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

    public static function getValidMtgoxCurrencyCodes()
    {
        return array(
            'USD',
            'AUD',
            'CAD',
            'CHF',
            'CNY',
            'DKK',
            'EUR',
            'GBP',
            'HKD',
            'JPY',
            'NZD',
            'PLN',
            'RUB',
            'SEK',
            'SGD',
            'THB',
        );
    }
}
