<?php

namespace Hyper\AdsBundle\Helper;

use Hyper\AdsBundle\Entity\BannerZoneReference;
use Hyper\AdsBundle\Exception\InvalidArgumentException;

class PaymentDaysCalculator
{
    private $bannerZone;
    private $days;
    private $orders;
    private $unifiedOrders;
    private $commonDays;
    private $excludedDays;

    public function __construct(BannerZoneReference $bannerZoneReference)
    {
        $this->bannerZone = $bannerZoneReference;
    }

    public function getNumberOfDaysToPay(\DateTime $from, \DateTime $to)
    {
        $this->validateDates($from, $to);
        $this->prepareUsedDaysAndOrders($to, $from);

        if (empty($this->orders)) {
            return $this->days;
        }
        $this->parseOrdersInDays($from, $to);

        return $this->days - $this->commonDays;
    }

    private function parseOrdersInDays($from, $to)
    {
        $this->setUnifiedOrders();
        $dateRange = $this->getDateRange($from, $to);
        $this->calculateCommonDays($dateRange);
    }

    private function prepareUsedDaysAndOrders($to, $from)
    {
        $this->setMaxDays($to, $from);
        $this->setOrders();
    }

    private function calculateCommonDays($dateRange)
    {
        $this->setDefaultDaysValues();

        foreach ($this->unifiedOrders as $order) {
            $this->checkOrderPaymentDatesInRange($dateRange, $order);
        }
    }

    private function checkOrderPaymentDatesInRange($dateRange, $order)
    {
        foreach ($dateRange as $date) {
            if (isset($this->excludedDays[$date->format('Ymd')])) {
                continue;
            }
            /** @var $date \DateTime */
            if ($date >= $order['from'] && $date < $order['to']) {
                $this->commonDays++;
                $this->excludedDays[$date->format('Ymd')] = 1;
            }
        }
    }

    private function setDefaultDaysValues()
    {
        $this->commonDays = 0;
        $this->excludedDays = array();
    }

    private function getDateRange($from, $to)
    {
        $dateInterval = new \DateInterval('P1D');
        $dateRange = new \DatePeriod($from, $dateInterval, $to);
        return $dateRange;
    }

    private function setUnifiedOrders()
    {
        $this->unifiedOrders = array();
        foreach ($this->orders as $order) {
            if (is_null($order->getPaymentTo())) {
                continue;
            }
            $toPayment = clone $order->getPaymentTo();
            $this->unifiedOrders[] = array(
                'from' => $order->getPaymentFrom(),
                'to' => $toPayment->modify('+1 day')
            );
        }
    }

    private function setOrders()
    {
        $this->orders = $this->bannerZone->getOrders();
    }

    private function setMaxDays($to, $from)
    {
        $to->modify('+1 day');
        $this->days = $from->diff($to)->days;
    }

    private function validateDates($from, $to)
    {
        if ($from > $to) {
            throw new InvalidArgumentException('{from} must be lower than or equal {to}');
        }
    }
}