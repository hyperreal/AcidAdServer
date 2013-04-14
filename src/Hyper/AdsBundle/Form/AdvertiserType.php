<?php

namespace Hyper\AdsBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class AdvertiserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('username', 'text', array('label' => 'name', 'translation_domain' => 'HyperAdsBundle'))
            ->add('email', 'email', array('label' => 'email', 'translation_domain' => 'HyperAdsBundle'))
            ->add('password', 'password', array('label' => 'password', 'translation_domain' => 'HyperAdsBundle'))
            ->add('firstName', 'text', array('label' => 'name.first', 'translation_domain' => 'HyperAdsBundle'))
            ->add('lastName', 'text', array('label' => 'name.last', 'translation_domain' => 'HyperAdsBundle'));
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            array(
                'data_class' => 'Hyper\AdsBundle\Entity\Advertiser'
            )
        );
    }

    public function getName()
    {
        return 'hyper_adsbundle_advertisertype';
    }
}
