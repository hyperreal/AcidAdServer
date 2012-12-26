<?php

namespace Wikp\ShoppingCartBundle\Cart;

interface ShopItemInterface
{
    /**
     * @return float
     */
    public function getPrice();

    /**
     * @return string
     */
    public function getCurrency();
}
