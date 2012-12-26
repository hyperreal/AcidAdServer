<?php

namespace Hyper\AdsBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Hyper\AdsBundle\DBAL\AnnouncementPaymentType;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 * @ORM\Table(name="announcement")
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorColumn("announcement_type", type="string")
 * @ORM\DiscriminatorMap({"announcement" = "Announcement", "banner" = "Banner"})
 */
class Announcement
{
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
     * @ORM\Column(type="date", name="expire_date")
     * @var \DateTime
     */
    protected $expireDate;

    /**
     * @ORM\Column(type="announcement_payment_type", name="announcement_payment_type")
     * @Assert\Choice(callback="getAnnouncementPaymentTypes")
     */
    protected $announcementPaymentType;

    /**
     * @ORM\Column(type="smallint", name="paid")
     */
    protected $paid = false;

    public function __construct()
    {
        $this->paid = false;
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

    public function getExpireDate()
    {
        return $this->expireDate;
    }

    public function setExpireDate(\DateTime $date)
    {
        $this->expireDate = $date;
    }

    public function isExpired()
    {
        return $this->getExpireDate() < new \DateTime();
    }

    public function setPaid($paid = true)
    {
        $this->paid = !!$paid;
    }

    public function getPaid()
    {
        return $this->paid;
    }

    public static function getAnnouncementPaymentTypes()
    {
        return AnnouncementPaymentType::getValidTypes();
    }
}
