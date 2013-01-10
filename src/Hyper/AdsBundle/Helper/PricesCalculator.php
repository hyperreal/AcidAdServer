<?php

namespace Hyper\AdsBundle\Helper;

use Doctrine\ORM\EntityManager;
use Hyper\AdsBundle\Entity\Announcement;
use Hyper\AdsBundle\Entity\Advertiser;
use Hyper\AdsBundle\Entity\Zone;
use Hyper\AdsBundle\Entity\BannerZoneReference;
use Hyper\AdsBundle\Exception\InvalidArgumentException;

class PricesCalculator
{
    const ROUND_PRECISION = 2;

    private $entityManager;

    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function getAmountToPayForAnnouncementInZone(Announcement $announcement, Zone $zone)
    {
        $expireDate = $announcement->getExpireDate();
        $toExpirationInterval = $expireDate->diff(new \DateTime());

        $normalPrice = $this->getDayPriceForZone($zone) * $toExpirationInterval->days;
        $discount = $normalPrice * $this->getPercentageDiscountForUser($announcement->getAdvertiser());

        $calculatedPrice = $normalPrice - $discount;

        return $this->getRoundedPrice($calculatedPrice);
    }

    private function getRoundedPrice($calculatedPrice)
    {
        return round($calculatedPrice, self::ROUND_PRECISION);
    }

    public function getPossibleDayPricesForAnnouncement(Announcement $announcement)
    {
        $zonePrices = $this->getActiveZonesPrices();

        $expireDate = $announcement->getExpireDate();
        $toExpirationInterval = $expireDate->diff(new \DateTime());

        $possiblePrices = array();
        $percentageDiscount = $this->getPercentageDiscountForUser($announcement->getAdvertiser());

        foreach ($zonePrices as $zoneId => $defaultPrice) {
            $calculatedPrice = ($defaultPrice - ($defaultPrice * $percentageDiscount)) * $toExpirationInterval->days;
            $possiblePrices[$zoneId] = $this->getRoundedPrice($calculatedPrice);
        }

        return $possiblePrices;
    }

    public function getActiveZonesPrices()
    {
        // TODO !!!!
        return array(
            1 => 10,
            self::ROUND_PRECISION => 10,
        );
    }

    public function getDayPriceForZone(Zone $zone)
    {
        return $zone->getID() * 10;//TODO !!!!
    }

    public function getPercentageDiscountForUser(Advertiser $advertiser)
    {
        return 0;
    }
}
