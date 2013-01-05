<?php

namespace Wikp\PaymentMtgoxBundle\Form\Validator;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class IsValidIpnSign extends Constraint
{
    public $messageInvalidObjectType = 'Type of object is invalid';
    public $messageInvalidSign = 'Rest-Sign does not match POST data';

    private $apiSecret;

    public function __construct($apiSecret)
    {
        $this->apiSecret = $apiSecret;
    }

    public function validatedBy()
    {
        return get_class($this) . 'Validator';
    }

    public function getApiSecret()
    {
        return $this->apiSecret;
    }
}
