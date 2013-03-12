<?php

namespace Hyper\AdsBundle\Helper;

use \DatePeriod as BaseDatePeriod;

class DatePeriod extends BaseDatePeriod
{
    /** @var \DateTime */
    private $start;

    /** @var \DateTime */
    private $end;

    public function __construct(\DateTime $start, \DateInterval $interval, \DateTime $end)
    {
        parent::__construct($start, $interval, $end);
        $this->start = $start;
        $this->end = $end;
    }

    /**
     * @return \DateTime
     */
    public function getStart()
    {
        return $this->start;
    }

    /**
     * @return \DateTime
     */
    public function getEnd()
    {
        return $this->end;
    }
}
