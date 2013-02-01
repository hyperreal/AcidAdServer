<?php

namespace Hyper\AdsBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class PaymentType extends AbstractType
{
    /** @var \DateTime */
    private $fromDate;

    /** @var \DateTime */
    private $toDate;

    public function setFromDate(\DateTime $from)
    {
        $this->fromDate = $from;
        $this->toDate = $from->modify('+1 month');
    }

    public function buildForm(FormBuilderInterface $formBuilder, array $options)
    {
        if (empty($this->fromDate)) {
            $this->fromDate = new \DateTime();
            $this->toDate = $this->fromDate->modify('+1 month');
        }
        $formBuilder->add('pay_from', 'date', $this->getOptions('pay.from', $this->fromDate));
        $formBuilder->add('pay_to', 'date', $this->getOptions('pay.to', $this->toDate));
    }

    public function getName()
    {
        return 'hyper_payment_form';
    }

    private function getOptions($label, \DateTime $time)
    {
        return array(
            'label' => $label,
            'translation_domain' => 'HyperAdsBundle',
            'property_path' => false,
            'data' => $time
        );
    }
}
