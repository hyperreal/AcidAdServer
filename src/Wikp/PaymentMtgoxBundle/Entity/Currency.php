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
     * @ORM\Column(type="decimal", scale=8, precision=15, name="buy_price")
     */
    private $buyPrice;

    /**
     * @ORM\Column(type="decimal", scale=8, precision=15, name="sell_price")
     */
    private $sellPrice;

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

    public function getSellPrice()
    {
        return $this->sellPrice;
    }

    public function setSellPrice($bidPrice)
    {
        $this->sellPrice = $bidPrice;
    }

    public function getBuyPrice()
    {
        return $this->buyPrice;
    }

    public function setBuyPrice($askPrice)
    {
        $this->buyPrice = $askPrice;
    }

    public function howMuchBitcoinsCanBuyForCurrency($currencyAmount)
    {
        return $this->buyPrice * $currencyAmount;
    }

    public function howMuchCurrencyCanBuyForBitcoins($bitcoinAmount)
    {
        return $this->sellPrice * $bitcoinAmount;
    }

    public function __toString()
    {
        return $this->name;
    }
}
