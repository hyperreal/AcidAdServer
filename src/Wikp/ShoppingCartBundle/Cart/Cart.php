<?php

namespace Wikp\ShoppingCartBundle\Cart;

use Symfony\Component\Security\Core\SecurityContext;
use Symfony\Component\HttpFoundation\Session\Session;
use Wikp\ShoppingCartBundle\Cart\AbstractCartItem;

class Cart
{
    /**
     * @var AbstractCartItem[]
     */
    private $items;
    private $session;
    private $securityContext;

    public function __construct(SecurityContext $context, Session $session)
    {
        $this->securityContext = $context;
        $this->session = $session;
        $this->items = array();
    }

    public function addToCart(AbstractCartItem $cartItem)
    {
        if (isset($this->items[$cartItem->getShopItemKey()])) {
            $this->items[$cartItem->getItemKey()]->addItems();
        } else {
            $this->items[$cartItem->getShopItemKey()] = $cartItem;
        }
    }

    public function removeFromCart(AbstractCartItem $cartItem)
    {
        $this->validateItemPresence($cartItem);

        $this->items[$cartItem->getShopItemKey()]->removeItems();
        if (0 === $this->items[$cartItem->getShopItemKey()]->getAmount()) {
            $this->purgeItem($cartItem);
        }
    }

    public function purgeItem($cartItem)
    {
        unset($this->items[$cartItem->getShopItemKey()]);
    }

    public function clear()
    {
        $this->items = array();
    }

    /**
     * @return array
     */
    public function getItems()
    {
        return $this->items;
    }

    /**
     * @param AbstractCartItem $cartItem
     * @return mixed
     * @throws \InvalidArgumentException
     */
    public function getItemAmount(AbstractCartItem $cartItem)
    {
        $this->validateItemPresence($cartItem);

        return $this->items[$cartItem->getShopItemKey()]->getAmount();
    }

    /**
     * @param $cartItem
     * @throws \InvalidArgumentException
     */
    protected function validateItemPresence(AbstractCartItem $cartItem)
    {
        if (empty($this->items[$cartItem->getShopItemKey()])) {
            throw new \InvalidArgumentException('Given item not found in cart');
        }
    }

    /**
     * @param AbstractCartItem $cartItem
     * @return float
     * @throws \InvalidArgumentException
     */
    public function getItemPrice(AbstractCartItem $cartItem)
    {
        $this->validateItemPresence($cartItem);

        return $cartItem->getShopItem()->getPrice();
    }
}
