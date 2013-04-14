<?php

namespace Hyper\AdsBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class PurifiedCkeditorType extends AbstractType
{
    private $purifierTransformer;

    public function __construct(DataTransformerInterface $purifierTransformer)
    {
        $this->purifierTransformer = $purifierTransformer;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addViewTransformer($this->purifierTransformer);
    }

    public function getParent()
    {
        return 'ckeditor';
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            array(
                'compound' => false,
            )
        );
    }

    public function getName()
    {
        return 'purified_ckeditor';
    }
}
