<?php

namespace Wikp\PaymentMtgoxBundle\Mtgox\RequestType;

use Wikp\PaymentMtgoxBundle\Mtgox\RequestTypeInterface;
use Wikp\PaymentMtgoxBundle\Mtgox\Request;
use Symfony\Component\HttpFoundation\ParameterBag;

class MtgoxTransactionUrlRequest implements RequestTypeInterface
{
    const METHOD_NAME = 'generic/private/merchant/order/create';

    private $currency;
    private $amount;
    private $returnSuccess;
    private $returnFailure;
    private $description;
    private $sendEmail;
    private $instantOnly = 1;

    /** @var \Symfony\Component\HttpFoundation\ParameterBag */
    private $parameters;

    public function setAmount($amount)
    {
        $this->amount = $amount;
    }

    public function setCurrency($currency)
    {
        $this->currency = $currency;
    }

    public function setDescription($description)
    {
        $this->description = $description;
    }

    public function setInstantOnly($instantOnly)
    {
        $this->instantOnly = $instantOnly;
    }

    public function setReturnFailure($returnFailure)
    {
        $this->returnFailure = $returnFailure;
    }

    public function setReturnSuccess($returnSuccess)
    {
        $this->returnSuccess = $returnSuccess;
    }

    public function setSendEmail($sendEmail)
    {
        $this->sendEmail = $sendEmail;
    }

    /**
     * @return void|\Wikp\PaymentMtgoxBundle\Mtgox\Request
     */
    public function asRequest()
    {
        $this->createParameters();
        return new Request(self::METHOD_NAME, $this->parameters);
    }

    private function createParameters()
    {
        $this->parameters = new ParameterBag();
        $this->parameters->set('currency', $this->currency);
        $this->parameters->set('amount', $this->amount);
        $this->parameters->set('return_success', $this->returnSuccess);
        $this->parameters->set('return_failure', $this->returnFailure);
        $this->parameters->set('instant_only', $this->instantOnly);

        if (!empty($this->description)) {
            $this->parameters->set('description', $this->description);
        }
        if (!empty($this->sendEmail) || $this->sendEmail === 0) {
            $this->parameters->set('email', $this->sendEmail);
        }
    }
}
