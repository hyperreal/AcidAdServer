<?php

namespace Wikp\PaymentMtgoxBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Wikp\PaymentMtgoxBundle\Form\Validator\IsValidIpnSign;

class MtgoxIpnType extends AbstractType
{
    const STATUS_PAID = 'paid';
    const STATUS_PARTIAL = 'partial';
    const STATUS_CANCELLED = 'cancelled';
    const STATUS_CLOSED = 'closed';

    private $apiSecret;

    public function __construct($apiSecret)
    {
        $this->apiSecret = $apiSecret;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $this->addRequiredFields($builder);
        $this->addIfPartialFields($builder);
        $this->addIfPaidFields($builder);

        return $builder;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            array(
                'csrf_protection' => false
            )
        );
    }

    public function getName()
    {
        return 'wikp_mtgox_ipn';
    }

    private function addRequiredFields(FormBuilderInterface $builder)
    {
        $builder->add('id', 'text', $this->getStandardOptionsRequired());
        $builder->add('payment_id', 'text', $this->getStandardOptionsRequired());
        $builder->add('data', 'integer', $this->getStandardOptionsRequired());
        $builder->add(
            'status',
            'choice',
            array(
                'property_path' => false,
                'required' => true,
                'choices' => array(
                    self::STATUS_PAID => self::STATUS_PAID,
                    self::STATUS_PARTIAL => self::STATUS_PARTIAL,
                    self::STATUS_CANCELLED => self::STATUS_CANCELLED,
                    self::STATUS_CLOSED => self::STATUS_CLOSED,
                )
            )
        );
        $builder->add(
            'ipnRequestObject',
            'text',
            $this->getStandardOptionsRequired(
                array(
                    new IsValidIpnSign($this->apiSecret)
                )
            )
        );
        $builder->add('aonce', 'text', $this->getStandardOptionsRequired());
    }

    private function addIfPartialFields(FormBuilderInterface $builder)
    {
        $builder->add('amount_pending', 'integer', $this->getStandardOptionsNotRequired());
        $builder->add('amount_valid', 'integer', $this->getStandardOptionsNotRequired());
        $builder->add('amount_total', 'integer', $this->getStandardOptionsNotRequired());
    }

    private function addIfPaidFields(FormBuilderInterface $builder)
    {
        $builder->add('amount', 'integer', $this->getStandardOptionsNotRequired());
        $builder->add('currency', 'text', $this->getStandardOptionsNotRequired());
        $builder->add('method', 'text', $this->getStandardOptionsNotRequired());
        $builder->add('date', 'integer', $this->getStandardOptionsNotRequired());
    }

    private function getStandardOptionsRequired(array $constraints = array())
    {
        if (empty($constraints)) {
            return array('property_path' => false, 'required' => true);
        }

        return array('property_path' => false, 'required' => true, 'constraints' => $constraints);
    }

    private function getStandardOptionsNotRequired(array $constraints = array())
    {
        if (empty($constraints)) {
            return array('property_path' => false, 'required' => false);
        }

        return array('property_path' => false, 'required' => false, 'constraints' => $constraints);
    }
}
