<?php

namespace Hyper\AdsBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Hyper\AdsBundle\DBAL\BannerType as BType;

class BannerType extends AbstractType
{
    private $addFileInput = true;

    public function disableFileInput()
    {
        $this->addFileInput = false;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        if ($this->addFileInput) {
            $builder
                ->add('file', 'file', array('label' => 'file', 'translation_domain' => 'HyperAdsBundle'));
        }

        $builder
            ->add('title', 'text', array('label' => 'title', 'translation_domain' => 'HyperAdsBundle'));

        $builder
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
