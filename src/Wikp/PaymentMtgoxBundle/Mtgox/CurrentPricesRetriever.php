<?php

namespace Wikp\PaymentMtgoxBundle\Mtgox;

use Doctrine\ORM\EntityManager;
use Wikp\PaymentMtgoxBundle\Entity\Currency;
use Wikp\PaymentMtgoxBundle\Mtgox\RequestType\CurrentPricesRequest;
use Wikp\PaymentMtgoxBundle\Mtgox\Client;

class CurrentPricesRetriever
{
    const RESPONSE_KEY_VALUE = 'value';
    const RESPONSE_KEY_BUY = 'buy';
    const RESPONSE_KEY_SELL = 'sell';

    /** @var \Doctrine\ORM\EntityManager */
    private $em;

    /** @var \Wikp\PaymentMtgoxBundle\Mtgox\Client */
    private $mtgoxClient;

    public function __construct(EntityManager $em, Client $mtgoxClient)
    {
        $this->em = $em;
        $this->mtgoxClient = $mtgoxClient;
    }

    public function updateCurrencyPrices(Currency $currency)
    {
        $prices = $this->getCurrentPricesForCurrency($currency);

        $currency->setBuyPrice($prices['buy']);
        $currency->setSellPrice($prices['sell']);

        $this->em->persist($currency);
        $this->em->flush($currency);

        return $currency;
    }

    protected function getCurrentPricesForCurrency(Currency $currency)
    {
        $request = new CurrentPricesRequest();
        $request->setCurrency($currency->getCode());
        $response = $this->doRawRequest($request);
        $buyPrices = $response->get(self::RESPONSE_KEY_BUY);
        $sellPrices = $response->get(self::RESPONSE_KEY_SELL);

        return array(
            'buy' => $buyPrices[self::RESPONSE_KEY_VALUE],
            'sell' => $sellPrices[self::RESPONSE_KEY_VALUE]
        );
    }

    protected function doRawRequest($request)
    {
        return $this->mtgoxClient->rawRequest($request->asRequest());
    }
}
