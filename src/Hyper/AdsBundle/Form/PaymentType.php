<?php

namespace Hyper\AdsBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormError;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Translation\TranslatorInterface;

class PaymentType extends AbstractType
{
    /** @var \DateTime */
    private $fromDate;

    /** @var \DateTime */
    private $toDate;

    private $maxOneBannerDays;

    /** @var \Symfony\Component\Translation\TranslatorInterface */
    private $translator;

    public function __construct($maxOneBannerDays, TranslatorInterface $translator)
    {
        $this->maxOneBannerDays = $maxOneBannerDays;
        $this->translator = $translator;
    }

    public function setFromDate(\DateTime $from)
    {
        $this->fromDate = $from;
        $this->setToDate();
    }

    public function buildForm(FormBuilderInterface $formBuilder, array $options)
    {
        if (empty($this->fromDate)) {
            $this->fromDate = new \DateTime();
            $this->setToDate();
        }
        $formBuilder->addEventListener(FormEvents::POST_BIND, array($this, 'validateTwoDates'));
        $formBuilder->add('pay_from', 'date', $this->getOptions('pay.from', $this->fromDate));
        $formBuilder->add('pay_to', 'date', $this->getOptions('pay.to', $this->toDate));
    }

    public function validateTwoDates(FormEvent $event)
    {
        $form = $event->getForm();
        $dateFrom = $form->get('pay_from')->getData();
        $dateTo = $form->get('pay_to')->getData();

        if ($dateTo->diff($dateFrom)->days > $this->maxOneBannerDays) {
            $form->get('pay_to')->addError(
                new FormError(
                    $this->translator->trans(
                        'max.days.error',
                        array('%maxDays%' => $this->maxOneBannerDays),
                        'HyperAdsBundle'
                    )
                )
            );
        }
    }

    private function setToDate()
    {
        $this->toDate = clone $this->fromDate;
        $this->toDate->modify('+1 month');
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            array(
                'translation_domain' => 'HyperAdsBundle',
            )
        );
    }

    public function getName()
    {
        return 'hyper_payment_form';
    }

    private function getOptions($label, \DateTime $time)
    {
        return array(
            'label' => $label,
            'translation_domain' => 'HyperAdsBundle',
            'mapped' => false,
            'data' => $time,
        );
    }
}
