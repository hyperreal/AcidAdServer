<?php

namespace Hyper\AdsBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use FOS\UserBundle\Form\Type\ProfileFormType as BaseType;


class EditProfileType extends BaseType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        $builder->add(
            'default_currency',
            'entity',
            array(
                'class' => 'WikpPaymentMtgoxBundle:Currency',
                'translation_domain' => 'HyperAdsBundle',
                'label' => 'default.currency',
            )
        );
    }

    public function getName()
    {
        return 'hyper_user_editprofile';
    }
}
