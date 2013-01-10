<?php

namespace Hyper\AdsBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Payment\CoreBundle\Entity\PaymentInstruction;
use Hyper\AdsBundle\Entity\Announcement;
use Wikp\PaymentMtgoxBundle\Plugin\OrderInterface;

/**
 * @ORM\Entity(repositoryClass="Hyper\AdsBundle\Entity\OrderRepository")
 * @ORM\Table(name="sent_order")
 */
class Order implements OrderInterface
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var \BannerZone
     *
     * @ORM\Id
     * @ORM\OneToOne(targetEntity="BannerZoneReference")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="banner_zone_id", referencedColumnName="id")
     * })
     */
    private $bannerZone;

    /**
     * @ORM\OneToOne(targetEntity="JMS\Payment\CoreBundle\Entity\PaymentInstruction")
     * @ORM\JoinColumn(name="payment_instruction_id", referencedColumnName="id")
     */
    private $paymentInstruction;

    /**
     * @var string
     *
     * @ORM\Column(name="order_number", type="string", length=255, nullable=false)
     */
    private $orderNumber;

    /**
     * @var float
     *
     * @ORM\Column(name="amount", type="decimal", nullable=true)
     */
    private $amount;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="payment_from", type="date", nullable=true)
     */
    private $paymentFrom;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="payment_to", type="date", nullable=true)
     */
    private $paymentTo;

    /**
     * @var integer
     *
     * @ORM\Column(name="clicks", type="integer", nullable=true)
     */
    private $clicks;

    /**
     * @var integer
     *
     * @ORM\Column(name="views", type="integer", nullable=true)
     */
    private $views;

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

    public function getPaymentFrom()
    {
        return $this->paymentFrom;
    }

    public function setPaymentFrom(\DateTime $paymentFrom)
    {
        $this->paymentFrom = $paymentFrom;
    }

    public function getViews()
    {
        return $this->views;
    }

    public function setViews($views)
    {
        $this->views = $views;
    }

    public function getClicks()
    {
        return $this->clicks;
    }

    public function setClicks($clicks)
    {
        $this->clicks = $clicks;
    }

    public function getBannerZoneReference()
    {
        return $this->bannerZone;
    }

    public function setBannerZoneReference(BannerZoneReference $reference)
    {
        $this->bannerZone = $reference;
    }

    public function approve()
    {
    }

    public function cancel()
    {
    }
}
