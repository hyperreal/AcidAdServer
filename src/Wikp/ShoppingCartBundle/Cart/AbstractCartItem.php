<?php

namespace Wikp\ShoppingCartBundle\Cart;

abstract class AbstractCartItem
{
    private $amount = 1;

    final public function addItems($number = 1)
    {
        if (!is_numeric($number)
            || $number < 1
            || str_replace('.', '', $number) != $number
        ) {
            throw new \InvalidArgumentException('Number must be an integer greater than zero');
        }

        $this->amount += $number;
    }

    final public function removeItems($number = 1)
    {
        if (!is_numeric($number)
            || $number > $this->amount
            || $number < 0
            || str_replace('.', '', $number) != $number
        ) {
            throw new \InvalidArgumentException('Number must be an integer lower than amount of previously added items.');
        }

        $this->amount -= $number;
    }

    final public function removeAllItems()
    {
        $this->amount = 0;
    }

    final public function getAmount()
    {
        return $this->amount;
    }

    /**
     * @return ShopItemInterface
     */
    abstract public function getShopItem();

    /**
     * @return string
     */
    abstract public function getShopItemKey();
}
