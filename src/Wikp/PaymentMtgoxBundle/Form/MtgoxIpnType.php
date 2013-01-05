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
                'data_class' => false,
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
        $builder->add('id', 'string', $this->getStandardOptionsRequired());
        $builder->add('payment_id', 'string', $this->getStandardOptionsRequired());
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
            'string',
            $this->getStandardOptionsRequired(
                array(
                    new IsValidIpnSign($this->apiSecret)
                )
            )
        );
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
        $builder->add('currency', 'string', $this->getStandardOptionsNotRequired());
        $builder->add('method', 'string', $this->getStandardOptionsNotRequired());
        $builder->add('date', 'datetime', $this->getStandardOptionsNotRequired());
    }

    private function getStandardOptionsRequired(array $constraints = array())
    {
        if (empty($constraints)) {
            return array('property_path' => false, 'required' => true);
        }

        return array('property_path' => false, 'required' => true, array('constraints' => $constraints));
    }

    private function getStandardOptionsNotRequired(array $constraints = array())
    {
        if (empty($constraints)) {
            return array('property_path' => false, 'required' => false);
        }

        return array('property_path' => false, 'required' => false, array('constraints' => $constraints));
    }
}
