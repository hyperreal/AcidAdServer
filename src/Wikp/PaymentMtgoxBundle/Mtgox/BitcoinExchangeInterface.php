<?php

namespace Wikp\PaymentMtgoxBundle\Mtgox;

interface BitcoinExchangeInterface
{
    public function convertToBitcoins($amount, $currencyCode = 'EUR');
    public function convertFromBitcoins($amount, $currencyCode = 'EUR');
}
