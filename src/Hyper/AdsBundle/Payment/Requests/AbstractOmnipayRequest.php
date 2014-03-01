<?php

namespace Hyper\AdsBundle\Payment\Requests;

abstract class AbstractOmnipayRequest
{
    abstract public function getStatus();
}