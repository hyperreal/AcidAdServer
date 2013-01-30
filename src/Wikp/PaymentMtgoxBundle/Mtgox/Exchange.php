<?php

namespace Wikp\PaymentMtgoxBundle\Mtgox;

use Doctrine\ORM\EntityManager;
use Wikp\PaymentMtgoxBundle\Exception\CurrencyException;
use Wikp\PaymentMtgoxBundle\Exception\InvalidArgumentException;
use Wikp\PaymentMtgoxBundle\Mtgox\BitcoinExchangeInterface;

class Exchange implements BitcoinExchangeInterface
{
    /** @var \Doctrine\ORM\EntityManager */
    private $em;

    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    public function convertFromBitcoins($amount, $currencyCode = 'EUR')
    {
        $this->throwUnlessValidParams($amount, $currencyCode);
        $currency = $this->getCurrencyEntity($currencyCode);

        return $amount * $currency->getSellPrice();
    }

    public function convertToBitcoins($amount, $currencyCode = 'EUR')
    {
        $this->throwUnlessValidParams($amount, $currencyCode);
        $currency = $this->getCurrencyEntity($currencyCode);

        return $amount * (1 / $currency->getBuyPrice());
    }

    /**
     * @param $currencyCode
     *
     * @return \Wikp\PaymentMtgoxBundle\Entity\Currency
     * @throws \Wikp\PaymentMtgoxBundle\Exception\CurrencyException
     */
    private function getCurrencyEntity($currencyCode)
    {
        /** @var $currencyRepository \Doctrine\ORM\EntityRepository */
        $currencyRepository = $this->em->getRepository('WikpPaymentMtgoxBundle:Currency');

        $currency = $currencyRepository->findOneBy(
            array(
                'code' => $currencyCode
            )
        );

        if (empty($currency)) {
            throw new CurrencyException('Currency of given code was not found');
        }

        return $currency;
    }

    /**
     * @param integer $amount
     * @param string $currencyCode
     *
     * @throws \Wikp\PaymentMtgoxBundle\Exception\InvalidArgumentException
     */
    private function throwUnlessValidParams($amount, $currencyCode)
    {
        if (!is_numeric($amount) || $amount < 0) {
            throw new InvalidArgumentException('amount must be a positive integer');
        }

        if (!is_string($currencyCode) || 3 != strlen($currencyCode)) {
            throw new InvalidArgumentException('Currency code is 3-char string');
        }
    }

}
