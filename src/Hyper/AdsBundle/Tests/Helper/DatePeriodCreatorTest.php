<?php

namespace Hyper\AdsBundle\Tests\Helper;

use Hyper\AdsBundle\Helper\DatePeriodCreator;

class DatePeriodCreatorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @expectedException Hyper\AdsBundle\Exception\InvalidArgumentException
     */
    public function testIntervalHigherThanDiffBetweenMaxAndMinDateCausesException()
    {
        new DatePeriodCreator($this->createDates(array(16)), new \DateInterval('P1Y'));
    }

    public function testGetPeriodsFromOneMonthWithOneDayBreak()
    {
        $interval = new \DateInterval('P1D');
        $dates = $this->createDates(array(16));
        $creator = new DatePeriodCreator($dates, $interval);
        $periods = $creator->getPeriods();

        $this->assertInternalType('array', $periods);
        $this->assertCount(2, $periods);
        $this->assertInstanceOf('Hyper\AdsBundle\Helper\DatePeriod', $periods[0]);
        $this->assertInstanceOf('Hyper\AdsBundle\Helper\DatePeriod', $periods[1]);

        $this->assertEquals('2013-03-01', $periods[0]->getStart()->format('Y-m-d'));
        $this->assertEquals('2013-03-15', $periods[0]->getEnd()->format('Y-m-d'));
        $this->assertEquals('2013-03-17', $periods[1]->getStart()->format('Y-m-d'));
        $this->assertEquals('2013-03-30', $periods[1]->getEnd()->format('Y-m-d'));
    }

    public function testGetPeriodsFromOneMonthWithMoreThanOneBreak()
    {
        $interval = new \DateInterval('P1D');
        $dates = $this->createDates(array(10, 15, 20, 21, 22));

        $creator = new DatePeriodCreator($dates, $interval);
        $periods = $creator->getPeriods();

        $this->assertInternalType('array', $periods);
        $this->assertCount(4, $periods);
        $this->assertInstanceOf('Hyper\AdsBundle\Helper\DatePeriod', $periods[0]);
        $this->assertInstanceOf('Hyper\AdsBundle\Helper\DatePeriod', $periods[1]);
        $this->assertInstanceOf('Hyper\AdsBundle\Helper\DatePeriod', $periods[2]);
        $this->assertInstanceOf('Hyper\AdsBundle\Helper\DatePeriod', $periods[3]);

        $this->assertEquals('2013-03-01', $periods[0]->getStart()->format('Y-m-d'));
        $this->assertEquals('2013-03-09', $periods[0]->getEnd()->format('Y-m-d'));
        $this->assertEquals('2013-03-11', $periods[1]->getStart()->format('Y-m-d'));
        $this->assertEquals('2013-03-14', $periods[1]->getEnd()->format('Y-m-d'));
        $this->assertEquals('2013-03-16', $periods[2]->getStart()->format('Y-m-d'));
        $this->assertEquals('2013-03-19', $periods[2]->getEnd()->format('Y-m-d'));
        $this->assertEquals('2013-03-23', $periods[3]->getStart()->format('Y-m-d'));
        $this->assertEquals('2013-03-30', $periods[3]->getEnd()->format('Y-m-d'));
    }

    public function testGetPeriodsFromMonthWithNoBrakes()
    {
        $interval = new \DateInterval('P1D');
        $dates = $this->createDates(array());

        $creator = new DatePeriodCreator($dates, $interval);
        $periods = $creator->getPeriods();

        $this->assertInternalType('array', $periods);
        $this->assertCount(1, $periods);
        $this->assertInstanceOf('Hyper\AdsBundle\Helper\DatePeriod', $periods[0]);

        $this->assertEquals('2013-03-01', $periods[0]->getStart()->format('Y-m-d'));
        $this->assertEquals('2013-03-30', $periods[0]->getEnd()->format('Y-m-d'));
    }

    private function createDates(array $exclude)
    {
        $dates = array();
        for ($i = 1; $i < 31; $i++) {
            if (!in_array($i, $exclude)) {
                $dates[] = new \DateTime('2013-03-' . sprintf('%02d', $i));
            }
        }

        return $dates;
    }
}
