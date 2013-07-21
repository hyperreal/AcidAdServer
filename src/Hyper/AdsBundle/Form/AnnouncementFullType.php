<?php

namespace Hyper\AdsBundle\Form;

use Hyper\AdsBundle\DBAL\AnnouncementPaymentType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class AnnouncementFullType extends AbstractType
{
    const FORM_NAME = 'annoucement_full_type';

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $choices = AnnouncementPaymentType::getValidTypesWithLabels();

        $builder->add('title', 'text', array('label' => 'title', 'translation_domain' => 'HyperAdsBundle'));
        $builder->add('adminDisabled', 'checkbox', $this->getStandardOptions('check.to.disable'));
        $builder->add(
            'advertiser',
            'entity',
            array(
                'class' => 'HyperAdsBundle:Advertiser',
                'property' => 'username',
                'required' => true,
                'label' => 'advertiser',
                'translation_domain' => 'HyperAdsBundle'
            )
        );
        $builder->add('paid', 'checkbox', $this->getStandardOptions('is.paid'));
        $builder->add('paidTo', 'date', $this->getStandardOptions('paid.to'));
        $builder->add(
            'announcementPaymentType',
            'choice',
            array(
                'label' => 'payment.type',
                'translation_domain' => 'HyperAdsBundle',
                'choices' => $choices,
            )
        );
        $builder->add(
            'description',
            'purified_ckeditor',
            array(
                'attr' => array('id' => "description"),
                'label' => 'description',
                'translation_domain' => 'HyperAdsBundle'
            )
        );
    }

    public function getName()
    {
        return self::FORM_NAME;
    }

    private function getStandardOptions($label)
    {
        return array('required' => false, 'label' => $label, 'translation_domain' => 'HyperAdsBundle');
    }
}
