<?php

namespace Hyper\AdsBundle\Form;

use Hyper\AdsBundle\DBAL\AnnouncementPaymentType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class AnnouncementType extends AbstractType
{
    const FORM_NAME = 'annoucement_type';

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $choices = AnnouncementPaymentType::getValidTypesWithLabels();
        unset($choices[AnnouncementPaymentType::ANNOUNCEMENT_PAYMENT_TYPE_PREMIUM]);

        $builder->add('title', 'text', array('label' => 'title', 'translation_domain' => 'HyperAdsBundle'));
        $builder->add(
            'disabled',
            'checkbox',
            array('required' => false, 'label' => 'check.to.disable', 'translation_domain' => 'HyperAdsBundle')
        );
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
