<?php

namespace Hyper\AdsBundle\Payment\BitPay;

use Omnipay\BitPay\Message\PurchaseRequest;

class BitPayPurchaseRequest extends PurchaseRequest
{
    public function getTransactionSpeed()
    {
        return $this->getParameter('transactionSpeed');
    }

    public function getFullNotifications()
    {
        return $this->getParameter('fullNotifications');
    }

    public function getNotifyEmail()
    {
        return $this->getParameter('notifyEmail');
    }

    public function getOrderId()
    {
        return $this->getParameter('orderId');
    }

    public function setTransactionSpeed($speed)
    {
        return $this->setParameter('transactionSpeed', $speed);
    }

    public function setNotifyEmail($email)
    {
        return $this->setParameter('notifyEmail', $email);
    }

    public function setFullNotifications($full)
    {
        return $this->setParameter('fullNotifications', !!$full);
    }

    public function setOrderId($orderId)
    {
        return $this->setParameter('orderId', $orderId);
    }

    public function getData()
    {
        $data = parent::getData();
        $data['transactionSpeed'] = $this->getTransactionSpeed();
        $data['fullNotifications'] = $this->getFullNotifications();
        $data['notificationEmail'] = $this->getNotifyEmail();
        $data['orderID'] = $this->getOrderId();

        return $data;
    }
}