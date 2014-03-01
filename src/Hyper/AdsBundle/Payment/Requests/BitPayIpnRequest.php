<?php

namespace Hyper\AdsBundle\Payment\Requests;

use Hyper\AdsBundle\Payment\BitPayStatus;
use Hyper\AdsBundle\Util\StandardInputReader;

class BitPayIpnRequest extends AbstractOmnipayRequest
{
    /** @var \Hyper\AdsBundle\Util\StandardInputReader */
    private $inputReader;

    private $decodedInput;

    public function __construct(StandardInputReader $inputReader)
    {
        $this->inputReader = $inputReader;
        $this->decodedInput = json_decode($inputReader->getStandardInput(), true);
    }

    public function isNew()
    {
        return BitPayStatus::STATUS_NEW == $this->getStatus();
    }

    public function getHash()
    {
        return $this->decodedInput['posData']['hash'];
    }

    public function getOrderId()
    {
        return $this->decodedInput['posData']['order_id'];
    }

    public function getKey()
    {
        return $this->decodedInput['posData']['key'];
    }

    public function getStatus()
    {
        return $this->decodedInput['status'];
    }

    public function getPrice()
    {
        return $this->decodedInput['price'];
    }

    public function getId()
    {
        return $this->decodedInput['id'];
    }
}