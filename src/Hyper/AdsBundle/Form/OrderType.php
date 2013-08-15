<?php

namespace Hyper\AdsBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Doctrine\ORM\EntityRepository;
use Hyper\AdsBundle\Entity\Advertisement;

class OrderType extends AbstractType
{
    /**
     * @var \Hyper\AdsBundle\Entity\Advertisement
     */
    private $announcement;

    public function setAnnouncement(Advertisement $announcement)
    {
        $this->announcement = $announcement;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        if (empty($this->announcement)) {
            throw new \LogicException('Advertisement has to be provided');
        }

        $announcementId = $this->announcement->getId();

        $builder->add(
            'announcement',
            'entity',
            array(
                'query_builder' => function (EntityRepository $repository) use ($announcementId) {
                    return $repository->createQueryBuilder('an')
                        ->where('an.id = ?1')
                        ->setParameter(1, $announcementId);
                },
                'class' => 'Hyper\AdsBundle\Entity\Advertisement',
                'data' => $this->announcement,
                'read_only' => true,
                'label' => 'announcement',
                'translation_domain' => 'HyperAdsBundle',
            )
        );

        $builder->add(
            'zone',
            'entity',
            array(
                'query_builder' => function (EntityRepository $repository) {
                    return $repository->createQueryBuilder('z')
                        ->where('z.enabled = ?1')
                        ->setParameter(1, true);
                },
                'class' => 'Hyper\AdsBundle\Entity\Zone',
                'mapped' => false,
                'label' => 'zone',
                'translation_domain' => 'HyperAdsBundle',
            )
        );

        $builder->add(
            'payment_to',
            'date',
            array(
                'label' => 'pay.to',
                'data' => $this->announcement->getExpireDate(),
                'translation_domain' => 'HyperAdsBundle',
            )
        );
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            array(
                'data_class' => 'Hyper\AdsBundle\Entity\Order',
            )
        );
    }

    public function getName()
    {
        return 'order_type';
    }
}
