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
        $builder
            ->add('file')
            ->add('type', 'choice', array('choices' => BType::getValidTypesWithLabels()))
            ->add('title')
            ->add(
                'expireDate',
                'date',
                array(
                    'label' => 'Expire date',
                    'data' => new \DateTime('+1 month'),
                )
            )
            ->add('linkTitle', 'text', array('label' => 'Link title'))
            ->add('url', 'url', array('label' => 'URL'))
            ->add('description', 'textarea');

        if (false) {
            $builder->add('campaign');
        }
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
