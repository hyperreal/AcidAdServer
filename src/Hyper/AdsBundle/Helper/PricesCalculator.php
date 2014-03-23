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
    private $factor;

    public function __construct(EntityManager $entityManager, $factor)
    {
        $this->entityManager = $entityManager;
        $this->factor = $factor;
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
        /** @var $zoneRepository \Hyper\AdsBundle\Entity\ZoneRepository */
        $zoneRepository = $this->entityManager->getRepository('HyperAdsBundle:Zone');
        return $this->getCompoundPrice($zone->getDailyPrice(), $zoneRepository->getCurrentNumberOfActiveBannersInZone($zone));
    }

    public function getCompoundPrice($price, $times)
    {
        for ($i = 0; $i < $times; $i++) {
            $price = $price + ($price * ($this->factor / 100));
        }

        return floatval($price);
    }

    /**
     * @param \Hyper\AdsBundle\Entity\Page[] $pages
     * @return array|\Hyper\AdsBundle\Entity\Page[]
     */
    public function updateDailyPricesInPages(array $pages)
    {
        foreach ($pages as &$page) {
            foreach ($page->getZones() as &$zone) {
                $zone->setDailyPrice($this->getDayPriceForZone($zone));
            }
        }

        return $pages;
    }

    public function getPercentageDiscountForUser(Advertiser $advertiser)
    {
        return 0;
    }
}
