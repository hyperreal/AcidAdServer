<?php

namespace Hyper\AdsBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Hyper\AdsBundle\DBAL\BannerType as BType;

class BannerType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('file')
            ->add(
                'type',
                'choice',
                array(
                    'choices' => BType::getValidTypesWithLabels(),
                    'label' => 'type',
                    'translation_domain' => 'HyperAdsBundle'
                )
            )
            ->add('title', 'text', array('label' => 'title', 'translation_domain' => 'HyperAdsBundle'))
            ->add(
                'expireDate',
                'date',
                array(
                    'label' => 'date.expire',
                    'data' => new \DateTime('+1 month'),
                    'translation_domain' => 'HyperAdsBundle'
                )
            )
            ->add('linkTitle', 'text', array('label' => 'linktitle', 'translation_domain' => 'HyperAdsBundle'))
            ->add('url', 'url', array('label' => 'url', 'translation_domain' => 'HyperAdsBundle'))
            ->add('description', 'textarea', array('label' => 'description', 'translation_domain' => 'HyperAdsBundle'));
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            array(
                'data_class' => 'Hyper\AdsBundle\Entity\Banner'
            )
        );
    }

    public function getName()
    {
        return 'hyper_adsbundle_bannertype';
    }
}
