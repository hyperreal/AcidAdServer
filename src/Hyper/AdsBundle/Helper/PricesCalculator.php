<?php

namespace Hyper\AdsBundle\Helper;

use Doctrine\ORM\EntityManager;
use Hyper\AdsBundle\Entity\Advertisement;
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

    public function getAmountToPayForAnnouncementInZone(Advertisement $announcement, Zone $zone)
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

    public function getPossibleDayPricesForAnnouncement(Advertisement $announcement)
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
        /** @var $zoneRepository \Hyper\AdsBundle\Entity\ZoneRepository */
        $zoneRepository = $this->entityManager->getRepository('HyperAdsBundle:Zone');
        return $zoneRepository->mapActiveZonesIdsToDailyPrice();
    }

    public function getDayPriceForZone(Zone $zone)
    {
        return $zone->getDailyPrice();
    }

    public function getPercentageDiscountForUser(Advertiser $advertiser)
    {
        return 0;
    }
}
