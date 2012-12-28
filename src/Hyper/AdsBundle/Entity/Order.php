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

    /** @ORM\Column(type="decimal", precision = 5, nullable=true) */
    private $amount;

    /**
     * @ORM\ManyToOne(targetEntity="Announcement", inversedBy="orders")
     * @ORM\JoinColumn(name="announcement_id", referencedColumnName="id")
     */
    private $announcement;

    /**
     * @var Zone
     * @ORM\ManyToOne(targetEntity="Zone", inversedBy="orders")
     * @ORM\JoinColumn(name="zone_id", referencedColumnName="id", nullable=true)
     */
    private $zone;

    /**
     * @ORM\Column(type="date", name="payment_to", nullable=true)
     * @var \DateTime
     */
    private $paymentTo;

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

    public function setPaymentTo(\DateTime $to)
    {
        $this->paymentTo = $to;
    }

    /**
     * @return \DateTime
     */
    public function getPaymentTo()
    {
        return $this->paymentTo;
    }

    /**
     * @return Zone
     */
    public function getZone()
    {
        if (!($this->getAnnouncement() instanceof Banner)) {
            return null;
        }

        return $this->zone;
    }

    public function setZone(Zone $zone)
    {
        if (!($this->getAnnouncement() instanceof Banner)) {
            throw new \LogicException('You can assign zone only to order for banner');
        }

        $this->zone = $zone;
    }

    public function getBannerZoneReference()
    {
        /** @var $announcement Banner */
        $announcement = $this->getAnnouncement();
        if (!($announcement instanceof Banner)) {
            return null;
        }

        return $announcement->getReferenceInZone($this->getZone()->getId());
    }

}
