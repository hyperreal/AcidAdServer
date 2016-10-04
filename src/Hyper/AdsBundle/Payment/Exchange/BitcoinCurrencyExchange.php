<?php

namespace Hyper\AdsBundle\Payment;

use Hyper\AdsBundle\Exception\PaymentException;


abstract class BitcoinCurrencyExchange
{
  const CURRENCY_CODE_BTC = 'BTC';

  abstract public function supportedCurrencies();
  abstract protected function exchange($amount, $fromCurrency, $toCurrency);

  public function toBTC($amount, $currency) {
    $this->checkCurrency($currency);
    return $this->exchange($amount, $currency, self::CURRENCY_CODE_BTC);
  }

  public function toCurrency($amount, $currency) {
    $this->checkCurrency($currency);
    return $this->exchange($amount, self::CURRENCY_CODE_BTC, $currency);
  }

  private function checkCurrency($currency) {
    $supportedCurrencies = $this->supportedCurrencies();
    if (!in_array($currency, $supportedCurrencies)) {
      throw new PaymentException("Currency $currency is not supported");
    }
  }
}
