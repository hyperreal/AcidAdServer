<?php

namespace Hyper\AdsBundle\Helper;

use Hyper\AdsBundle\Exception\InvalidArgumentException;
use Hyper\AdsBundle\Helper\DatePeriod;

/**
 * Purpose of this class is to split array of \DateTime objects that are expected to be almost consecutive
 * (with some $interval) into array of \DatePeriod objects.
 *
 * For example:
 *
 * An array of objects representing all (but one) days in a month will be converted into the array of two \DatePeriod
 * objects.
 * [2013-02-01, 2013-02-02, ..., 2013-02-15, (there is no 2012-02-16!) 2013-02-17, 2012-02-18...2012-02-29] will be:
 * [(2013-02-01, 2013-02-15), (2013-02-17, 2013-02-29)]
 */
class DatePeriodCreator
{
    /** @var \DateTime[] */
    private $dates;

    /** @var \DateInterval */
    private $interval;

    /** @var \Hyper\AdsBundle\Helper\DatePeriod[] */
    private $periods;

    public function __construct(array $dates, \DateInterval $interval)
    {
        /** @var $maxDate \DateTime */
        $maxDate = max($dates);
        $currentMaxDate = clone $maxDate;
        $currentMaxDate->add($interval);
        $dates[] = $currentMaxDate;
        $this->dates = array_values($dates);
        $this->interval = $interval;
    }

    /**
     * @return \Hyper\AdsBundle\Helper\DatePeriod[]
     */
    public function getPeriods()
    {
        if (empty($this->periods)) {
            $this->createPeriods();
        }

        return $this->periods;
    }

    private function createPeriods()
    {
        $datesCount = count($this->dates);
        $firsts = array($this->dates[0]);
        $lasts = array();
        for ($i = 0; $i < $datesCount; $i++) {

            if ($i == $datesCount - 1) {
                $lasts[] = $this->dates[$i];
                break;
            }

            $nextDateByInterval = clone $this->dates[$i];
            $nextDateByInterval->add($this->interval);
            $nextInArray = $this->dates[$i + 1];
            if ($nextInArray->format('Y-m-d') != $nextDateByInterval->format('Y-m-d')) {
                $lasts[] = $nextDateByInterval;
                $firsts[] = $nextInArray;
            }
        }

        $interval = $this->interval;
        $this->periods =  array_map(
            function (\DateTime $start, \DateTime $end) use ($interval) {
                return new DatePeriod($start, $interval, $end);
            },
            $firsts,
            $lasts
        );
    }

    private function getIntervalInSeconds(\DateInterval $interval)
    {
        return $interval->s
            + $interval->i * 60
            + $interval->h * 3600
            + $interval->d * 86400
            + $interval->m * 2592000
            + $interval->y * 946080000;
    }
}
