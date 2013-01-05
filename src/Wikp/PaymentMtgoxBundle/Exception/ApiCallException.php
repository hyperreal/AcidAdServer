<?php

namespace Wikp\PaymentMtgoxBundle\Exception;

use Wikp\PaymentMtgoxBundle\Mtgox\Request;

class ApiCallException extends \RuntimeException
{
    private $request;

    public function setRequest(Request $request)
    {
        $this->request = $request;
    }

    public function getRequest()
    {
        return $this->request;
    }
}
