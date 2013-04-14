<?php

namespace Hyper\AdsBundle\Form;

use FOS\UserBundle\Form\Type\RegistrationFormType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Translation\TranslatorInterface;

class RegistrationType extends RegistrationFormType
{
    /** @var \Symfony\Component\Translation\TranslatorInterface */
    private $translator;
    /** @var \Symfony\Component\Routing\RouterInterface */
    private $router;

    public function __construct($class, TranslatorInterface $translator, RouterInterface $router)
    {
        parent::__construct($class);
        $this->translator = $translator;
        $this->router = $router;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);
        $label = $this->translator->trans(
            'rules.accept',
            array(
                '%rules_url%' => $this->router->generate('default_rules', array(), true)
            ),
            'HyperAdsBundle'
        );
        $builder->add(
            'acceptRules',
            'checkbox',
            array(
                'label' => $label,
                'required' => true,
                'property_path' => false
            )
        );
    }

    public function getName()
    {
        return 'hyper_user_register';
    }
}