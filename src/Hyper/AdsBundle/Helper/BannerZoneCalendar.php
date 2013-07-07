<?php

namespace Hyper\AdsBundle\Helper;

use Doctrine\Common\Cache\CacheProvider;
use Doctrine\ORM\EntityManager;
use Hyper\AdsBundle\Entity\Zone;
use Hyper\AdsBundle\Helper\DatePeriodCreator;

class BannerZoneCalendar
{
    const DATE_FORMAT = 'Y-m-d';

    const CACHE_PREFIX = 'cal_';
    const CACHE_ALL_PREFIX = 'all_set_';
    const MAX_BANNERS = 5;

    /** @var \Doctrine\Common\Cache\CacheProvider */
    private $cache;

    /** @var \Doctrine\ORM\EntityManager */
    private $em;

    public function __construct(CacheProvider $cache, EntityManager $em)
    {
        $this->cache = $cache;
        $this->em = $em;
    }

    /**
     * @param \Hyper\AdsBundle\Entity\Zone $zone
     * @param \DateTime $from
     * @param \DateTime $to
     *
     * @return \Hyper\AdsBundle\Helper\DatePeriod[]
     */
    public function getCommonDaysForZone(Zone $zone, \DateTime $from, \DateTime $to)
    {
        //przerobić na event po ipn:success
        //if (!$this->cache->contains(self::CACHE_ALL_PREFIX . $zone->getId())) {
            $this->warmUp($zone);
        //}

        $period = $this->getPeriodBetweenTwoDates($from, $to);
        $zoneId = $zone->getId();
        $commonDays = array();
        $oneDayInterval = new \DateInterval('P1D');

        foreach ($period as $date) {
            $dateString = $date->format(self::DATE_FORMAT);
            $cacheId = self::CACHE_PREFIX . $zoneId . '_' . $dateString;
            $value = $this->cache->fetch($cacheId);
            if (false !== $value && $value >= self::MAX_BANNERS) {
                $commonDays[$dateString] = $date;
            }
        }

        if (empty($commonDays)) {
            return array();
        }
        $datePeriodCreator = new DatePeriodCreator($commonDays, $oneDayInterval);

        return $datePeriodCreator->getPeriods();
    }

    public function createOccupancyReport(\DateTime $from, \DateTime $to)
    {
        $period = $this->getPeriodBetweenTwoDates($from, $to);
        $zones = $this->em->getRepository('HyperAdsBundle:Zone')->findAll();
        $occupancy = array();

        foreach ($period as $date) {
            /** @var $date \DateTime */
            $occupancy[$date->format('Y-m-d')] = $this->getNumberOfBannersOfAllZonesInGivenDay($date, $zones);
        }

        return $occupancy;
    }

    /**
     * @param \DateTime $date
     * @param \Hyper\AdsBundle\Entity\Zone[] $zones
     * @return array
     */
    private function getNumberOfBannersOfAllZonesInGivenDay(\DateTime $date, $zones)
    {
        static $warmUpZones = array();
        $formattedDate = $date->format(self::DATE_FORMAT);
        $zonesNumberMap = array();

        foreach ($zones as $zone) {
            if (!isset($warmUpZones[$zone->getId()])) {
                $this->warmUp($zone);
                $warmUpZones[$zone->getId()] = true;
            }
            $cacheId = self::CACHE_PREFIX . $zone->getId() . '_' . $formattedDate;
            $zonesNumberMap[$zone->getId() . '_' . $zone->getName()] = intval($this->cache->fetch($cacheId));
        }

        return $zonesNumberMap;
    }

    private function warmUp(Zone $zone)
    {
        /** @var $orderRepository \Hyper\AdsBundle\Entity\OrderRepository */
        $orderRepository = $this->em->getRepository('HyperAdsBundle:Order');
        $ordersInZone = $orderRepository->getOrdersForZone($zone);
        $dailyInterval = new \DateInterval('P1D');
        $days = array();
        foreach ($ordersInZone as $order) {
            $period = new \DatePeriod($order->getPaymentFrom(), $dailyInterval, $order->getPaymentTo());
            $this->insertBannersDays($period, $days);
        }

        $this->saveDaysInCache($days, $zone->getId());
    }

    private function saveDaysInCache($days, $zoneId)
    {
        foreach ($days as $dayString => $numOfBanners) {
            $cacheId = self::CACHE_PREFIX . $zoneId . '_' . $dayString;
            $this->cache->save($cacheId, $numOfBanners);
        }
        $this->cache->save(self::CACHE_ALL_PREFIX . $zoneId, true);
    }

    private function insertBannersDays($period, &$days)
    {
        foreach ($period as $date) {
            /** @var $date \DateTime */
            $dateString = $date->format(self::DATE_FORMAT);
            if (!array_key_exists($dateString, $days)) {
                $days[$dateString] = 1;
            } else {
                $days[$dateString]++;
            }
        }
    }

    /**
     * @param \DateTime $from
     * @param \DateTime $to
     * @return \DatePeriod
     */
    private function getPeriodBetweenTwoDates(\DateTime $from, \DateTime $to)
    {
        $oneDayInterval = new \DateInterval('P1D');
        $to->add($oneDayInterval);
        $period = new \DatePeriod($from, $oneDayInterval, $to);
        return $period;
    }
}
