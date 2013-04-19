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
        $builder->add('adminDisabled', 'checkbox', array('required' => false, 'label' => 'check.to.disable', 'translation_domain' => 'HyperAdsBundle'));
        $builder->add('advertiser', 'advertiser', array('required' => true, 'label' => 'advertiser', 'translation_domain' => 'HyperAdsBundle'));
        $builder->add('addDate', 'date', array('required' => true, 'label' => 'add.date', 'translation_domain' => 'HyperAdsBundle'));
        $builder->add('paid', 'checkbox', array('required' => false, 'label' => 'is.paid', 'translation_domain' => 'HyperAdsBundle'));
        $builder->add('paidTo', 'date', array('required' => false, 'label' => 'paid.to', 'translation_domain' => 'HyperAdsBundle'));
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
}
