<?php

namespace Hyper\AdsBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Hyper\AdsBundle\Entity\Advertisement;
use Hyper\AdsBundle\DBAL\AnnouncementPaymentType;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="Hyper\AdsBundle\Entity\AdvertisementRepository")
 */
class Announcement extends Advertisement
{

    /**
     * @ORM\Column(type="announcement_payment_type", name="announcement_payment_type")
     * @Assert\Choice(callback="getAnnouncementPaymentTypes")
     */
    private $announcementPaymentType;

    public function __construct()
    {
        parent::__construct();
        $this->paid = AnnouncementPaymentType::ANNOUNCEMENT_PAYMENT_TYPE_STANDARD != $this->announcementPaymentType;
    }

    public function setAnnouncementPaymentType($announcementPaymentType)
    {
        if (!in_array($announcementPaymentType, AnnouncementPaymentType::getValidTypes())) {
            throw new \InvalidArgumentException('Given announcement payment type is invalid');
        }

        $this->announcementPaymentType = $announcementPaymentType;
    }

    public function getAnnouncementPaymentType()
    {
        return $this->announcementPaymentType;
    }

    public function isActive()
    {
        if (AnnouncementPaymentType::ANNOUNCEMENT_PAYMENT_TYPE_STANDARD == $this->announcementPaymentType) {
            return true;
        } else {
            return parent::isActive();
        }
    }

    public static function getAnnouncementPaymentTypes()
    {
        return AnnouncementPaymentType::getValidTypes();
    }
}
