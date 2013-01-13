<?php

namespace Wikp\PaymentMtgoxBundle\Form\Validator;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Wikp\PaymentMtgoxBundle\Form\IpnRequest;

class IsValidIpnSignValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint)
    {
        /** @var $value \Wikp\PaymentMtgoxBundle\Form\IpnRequest */
        if (!($value instanceof IpnRequest)) {
            $this->context->addViolation($constraint->messageInvalidObjectType);
        }

        if (!$this->validateRestSign($value, $constraint->getApiSecret())) {
            $this->context->addViolation($constraint->messageInvalidSign);
        }
    }

    private function validateRestSign(IpnRequest $value, $apiSecret)
    {
        $validSign = hash_hmac(
            'sha512',
            $value->getRawPost(),
            base64_decode($apiSecret),
            true
        );

        return $validSign == base64_decode($value->getHeaderRestSign());
    }
}
