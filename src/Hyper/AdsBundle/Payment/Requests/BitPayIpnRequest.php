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
        if (isset($this->decodedInput['posData'])) {
            $this->decodedInput['posData'] = json_decode($this->decodedInput['posData'], true);
        }
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
        return $this->decodedInput['posData']['posData']['order'];
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

    public function hasOrderId()
    {
        return array_key_exists('posData', $this->decodedInput)
            && array_key_exists('posData', $this->decodedInput['posData'])
            && array_key_exists('order', $this->decodedInput['posData']['posData']);
    }
}