<?php

namespace Wikp\PaymentMtgoxBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class MtgoxInstantType extends AbstractType
{
    public function buildForm(FormBuilderInterface $formBuilder, array $options)
    {

    }

    public function getName()
    {
        return 'mtgox_instant_payment';
    }
}
