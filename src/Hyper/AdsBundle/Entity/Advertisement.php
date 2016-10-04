<?php

namespace Hyper\AdsBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @ORM\Entity(repositoryClass="Hyper\AdsBundle\Entity\AdvertisementRepository")
 * @ORM\Table(name="announcement")
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorColumn("announcement_type", type="string")
 * @ORM\DiscriminatorMap({"announcement" = "Announcement", "banner" = "Banner"})
 */
abstract class Advertisement
{
    const TYPE_ANNOUNCEMENT = 'announcement';
    const TYPE_BANNER = 'banner';

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected $title;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    protected $description;

    /**
     * @ORM\ManyToOne(targetEntity="Advertiser", inversedBy="advertisements")
     * @ORM\JoinColumn(name="advertiser_id", referencedColumnName="id")
     *
     * @var \Hyper\AdsBundle\Entity\Advertiser
     */
    protected $advertiser;

    /**
     * @ORM\Column(type="boolean", name="paid")
     */
    protected $paid = false;

    /**
     * @ORM\Column(type="datetime", name="add_date")
     * @var \DateTime
     */
    protected $addDate;

    /**
     * @ORM\Column(type="datetime", name="modification_date", nullable=true)
     * @var \DateTime
     */
    protected $modificationDate;

    /**
     * @ORM\Column(type="boolean", name="expired")
     */
    protected $expired;

    /**
     * @ORM\Column(type="date", name="paid_to", nullable=true)
     * @var \DateTime
     */
    protected $paidTo;

    /**
     * @ORM\OneToMany(targetEntity="Order", mappedBy="announcement", cascade={"persist", "remove"})
     */
    protected $orders;

    /**
     * @ORM\OneToMany(targetEntity="AdvertisementReport", mappedBy="advertisement", cascade={"persist", "remove"})
     * @var AdvertisementReport[]
     */
    protected $reports;

    public function __construct()
    {
        $this->orders = new ArrayCollection();
        $this->addDate = new \DateTime();
        $this->expired = false;
    }

    public function setId($id)
    {
        $this->id = $id;
    }

    public function getId()
    {
        return $this->id;
    }

    public function setTitle($title)
    {
        $this->title = $title;
    }

    public function getTitle()
    {
        return $this->title;
    }

    public function getDescription()
    {
        return $this->description;
    }

    public function setDescription($description)
    {
        $this->description = strval($description);
    }

    public function isActive()
    {
        return $this->getPaidTo() > new \DateTime();
    }

    public function setPaid($paid = true)
    {
        $this->paid = !!$paid;
    }

    public function getPaid()
    {
        return $this->paid;
    }

    public function isPaid()
    {
        return $this->getPaid();
    }

    public function setAddDate(\DateTime $addDate)
    {
        $this->addDate = $addDate;
    }

    /**
     * @return \DateTime
     */
    public function getAddDate()
    {
        return $this->addDate;
    }

    public function markAsModified()
    {
        $this->modificationDate = new \DateTime();
    }

    /**
     * @return \DateTime
     */
    public function getModificationDate()
    {
        return $this->modificationDate;
    }

    public function isExpired()
    {
        return $this->expired;
    }

    public function setExpired($expired)
    {
        $this->expired = !!$expired;
    }

    /**
     * @return Advertiser
     */
    public function getAdvertiser()
    {
        return $this->advertiser;
    }

    public function setAdvertiser(Advertiser $advertiser)
    {
        $this->advertiser = $advertiser;
    }

    /**
     * @return \DateTime
     */
    public function getPaidTo()
    {
        return $this->paidTo;
    }

    public function setPaidTo(\DateTime $paidTo = null)
    {
        $this->paidTo = $paidTo;
    }

    /**
     * @return Order[]
     */
    public function getOrders()
    {
        return $this->orders;
    }

    /**
     * @return AdvertisementReport[]
     */
    public function getReports()
    {
        return $this->reports;
    }

    public function __toString()
    {
        return sprintf('%s (ID: %d)', $this->getTitle(), $this->getId());
    }
}
