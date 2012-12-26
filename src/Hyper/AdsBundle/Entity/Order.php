<?php

namespace Hyper\AdsBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Payment\CoreBundle\Entity\PaymentInstruction;
use Hyper\AdsBundle\Entity\Announcement;

/**
 * @ORM\Entity()
 * @ORM\Table(name="sent_order")
 */
class Order
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\OneToOne(targetEntity="JMS\Payment\CoreBundle\Entity\PaymentInstruction")
     * @ORM\JoinColumn(name="payment_instruction_id", referencedColumnName="id")
     */
    private $paymentInstruction;

    /** @ORM\Column(name="order_number", type="string", unique = true) */
    private $orderNumber;

    /** @ORM\Column(type="decimal", precision = 5) */
    private $amount;

    /** @ORM\OneToOne(targetEntity="Announcement") */
    private $announcement;

    public function getId()
    {
        return $this->id;
    }

    public function getPaymentInstruction()
    {
        return $this->paymentInstruction;
    }

    public function setPaymentInstruction(PaymentInstruction $paymentInstruction)
    {
        $this->paymentInstruction = $paymentInstruction;
    }

    public function getOrderNumber()
    {
        return $this->orderNumber;
    }

    public function setOrderNumber($orderNumber)
    {
        $this->orderNumber = $orderNumber;
    }

    public function getAmount()
    {
        return $this->amount;
    }

    public function setAmount($amount)
    {
        $this->amount = (float)$amount;
    }

    public function getAnnouncement()
    {
        return $this->announcement;
    }

    public function setAnnouncement(Announcement $announcement)
    {
        $this->announcement = $announcement;
    }
}
