<?php

namespace Wikp\PaymentMtgoxBundle\Form;

class IpnRequest
{
    private $rawPost;
    private $headerRestSign;

    public function __construct($rawPost, $headerRestSign)
    {
        $this->rawPost = $rawPost;
        $this->headerRestSign = $headerRestSign;
    }

    public function getRawPost()
    {
        return $this->rawPost;
    }

    public function getHeaderRestSign()
    {
        return $this->headerRestSign;
    }
}
