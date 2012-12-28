<?php

namespace Hyper\AdsBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class PaymentType extends AbstractType
{
    public function buildForm(FormBuilderInterface $formBuilder, array $options)
    {
        $formBuilder->add('pay_to', 'date', $this->getOptions('pay.to'));
    }

    public function getName()
    {
        return 'hyper_payment_form';
    }

    private function getOptions($label)
    {
        return array('label' => $label, 'translation_domain' => 'HyperAdsBundle', 'property_path' => false);
    }
}
