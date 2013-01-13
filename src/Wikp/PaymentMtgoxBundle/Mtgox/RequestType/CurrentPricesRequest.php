<?php

namespace Wikp\PaymentMtgoxBundle\Mtgox\RequestType;

use Wikp\PaymentMtgoxBundle\Mtgox\RequestTypeInterface;
use Wikp\PaymentMtgoxBundle\Mtgox\Request;
use Wikp\PaymentMtgoxBundle\Exception\InvalidArgumentException;
use Wikp\PaymentMtgoxBundle\Plugin\MtgoxPaymentPlugin;
use Symfony\Component\HttpFoundation\ParameterBag;

class CurrentPricesRequest implements RequestTypeInterface
{
    const METHOD_NAME = '%s%s/ticker';
    const BTC_CURRENCY_CODE = 'BTC';

    private $currency;

    public function setCurrency($currencyCode)
    {
        if (!in_array($currencyCode, MtgoxPaymentPlugin::getValidMtgoxCurrencyCodes())) {
            throw new InvalidArgumentException('Given currency code is invalid');
        }

        $this->currency = $currencyCode;
    }

    /**
     * @return \Wikp\PaymentMtgoxBundle\Mtgox\Request
     */
    public function asRequest()
    {
        return new Request(
            sprintf(self::METHOD_NAME, self::BTC_CURRENCY_CODE, $this->currency),
            new ParameterBag()
        );
    }
}
