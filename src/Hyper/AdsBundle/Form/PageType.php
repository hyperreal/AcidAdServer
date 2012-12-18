<?php

namespace Hyper\AdsBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class PageType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', 'text', array('label' => 'name', 'translation_domain' => 'HyperAdsBundle'))
            ->add('url', 'url', array('label' => 'url', 'translation_domain' => 'HyperAdsBundle'));
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            array(
                'data_class' => 'Hyper\AdsBundle\Entity\Page'
            )
        );
    }

    public function getName()
    {
        return 'hyper_adsbundle_pagetype';
    }
}
