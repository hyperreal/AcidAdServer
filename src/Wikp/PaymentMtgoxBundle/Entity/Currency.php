<?php

namespace Wikp\PaymentMtgoxBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="currency")
 */
class Currency
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=3)
     */
    private $code;

    /**
     * @ORM\Column(type="string")
     */
    private $name;

    /**
     * @ORM\Column(type="decimal", scale=8, precision=15, name="ask_price")
     */
    private $askPrice;

    /**
     * @ORM\Column(type="decimal", scale=8, precision=15, name="bid_price")
     */
    private $bidPrice;

    public function getId()
    {
        return $this->id;
    }

    public function setId($id)
    {
        $this->id = $id;
    }

    public function getCode()
    {
        return $this->code;
    }

    public function setCode($code)
    {
        $this->code = $code;
    }

    public function getName()
    {
        return $this->name;
    }

    public function setName($name)
    {
        $this->name = $name;
    }

    public function getBidPrice()
    {
        return $this->bidPrice;
    }

    public function setBidPrice($bidPrice)
    {
        $this->bidPrice = $bidPrice;
    }

    public function getAskPrice()
    {
        return $this->askPrice;
    }

    public function setAskPrice($askPrice)
    {
        $this->askPrice = $askPrice;
    }

    public function howMuchBitcoinsCanBuyForCurrency($currencyAmount)
    {
        return $this->askPrice * $currencyAmount;
    }

    public function howMuchCurrencyCanBuyForBitcoins($bitcoinAmount)
    {
        return $this->bidPrice * $bitcoinAmount;
    }
}
