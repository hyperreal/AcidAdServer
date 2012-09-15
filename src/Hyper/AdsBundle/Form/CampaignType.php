<?php

namespace Hyper\AdsBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Translation\TranslatorInterface;

class CampaignType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name')
            ->add('startDate', 'date', array(
                'data' => new \DateTime(),
            ))
            ->add('expireDate', 'date', array(
                'data' => new \DateTime('+1 month'),
            ))
            ->add('advertiser')
        ;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Hyper\AdsBundle\Entity\Campaign'
        ));
    }

    public function getName()
    {
        return 'hyper_adsbundle_campaigntype';
    }
}
