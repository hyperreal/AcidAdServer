<?php

namespace Hyper\AdsBundle\Tests\Helper;

use Hyper\AdsBundle\Entity\Order;
use Hyper\AdsBundle\Entity\BannerZoneReference;
use Hyper\AdsBundle\Helper\PaymentDaysCalculator;

class PaymentDaysCalculatorTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @covers \Hyper\AdsBundle\Helper\PaymentDaysCalculator::getNumberOfDaysToPay
     * @dataProvider numbersOfDaysToPayProvider
     */
    public function testGetNumbersOfDaysToPay(array $orderDates, $from, $to, $expectedDaysToPay)
    {
        $daysCalculator = new PaymentDaysCalculator($this->getBannerZoneReferenceMock($orderDates));

        $this->assertEquals(
            $expectedDaysToPay,
            $daysCalculator->getNumberOfDaysToPay(
                new \DateTime($from),
                new \DateTime($to)
            )
        );
    }

    /**
     * @covers \Hyper\AdsBundle\Helper\PaymentDaysCalculator::getNumberOfDaysToPay
     * @expectedException Hyper\AdsBundle\Exception\InvalidArgumentException
     * @expectedExceptionMessage {from} must be lower than or equal {to}
     */
    public function testGetNumbersOfDaysToPayWithFromLessThanTo()
    {
        $daysCalculator = new PaymentDaysCalculator($this->getBannerZoneReferenceMock(array()));

        $daysCalculator->getNumberOfDaysToPay(
            new \DateTime('2012-08-04'),
            new \DateTime('2012-08-01')
        );
    }

    public function numbersOfDaysToPayProvider()
    {
        return array(
            array(array(), '2012-08-03', '2012-08-03', 1),
            array(array(), '2012-08-03', '2012-08-04', 2),
            array(array(), '2011-08-03', '2012-08-03', 367), //leap-year
            array(array(), '2010-08-03', '2011-08-03', 366), //normal year

            array(array(array('2012-08-01', '2012-08-10')), '2012-08-01', '2012-08-10', 0),
            array(array(array('2012-08-01', '2012-08-10')), '2012-08-01', '2012-08-12', 2),
            array(array(array('2012-08-01', '2012-08-10')), '2012-08-01', '2012-08-06', 0),
            array(array(array('2012-08-01', '2012-08-10')), '2012-07-30', '2012-08-06', 2),
            array(array(array('2012-08-01', '2012-08-10')), '2012-07-30', '2012-08-10', 2),
            array(array(array('2012-08-01', '2012-08-10')), '2012-07-30', '2012-08-14', 6),

            array(array(array('2012-08-01', '2012-08-10'), array('2012-08-13', '2012-08-16')), '2012-07-30', '2012-08-14', 4),
            array(array(array('2012-08-01', '2012-08-10'), array('2012-08-13', '2012-08-16')), '2012-08-01', '2012-08-16', 2),
            array(array(array('2012-08-01', '2012-08-10'), array('2012-08-13', '2012-08-16')), '2012-08-01', '2012-08-10', 0),
            array(array(array('2012-08-01', '2012-08-10'), array('2012-08-13', '2012-08-16')), '2012-08-10', '2012-08-12', 2),
            array(array(array('2012-08-01', '2012-08-10'), array('2012-08-13', '2012-08-16')), '2012-08-10', '2012-08-16', 2),
            array(array(array('2012-08-01', '2012-08-10'), array('2012-08-13', '2012-08-16')), '2012-08-10', '2012-08-18', 4),
            array(array(array('2012-08-01', '2012-08-10'), array('2012-08-13', '2012-08-16')), '2012-08-11', '2012-08-11', 1),
            array(array(array('2012-08-01', '2012-08-10'), array('2012-08-13', '2012-08-16')), '2012-08-10', '2012-08-10', 0),
            array(array(array('2012-08-01', '2012-08-10'), array('2012-08-13', '2012-08-16')), '2012-08-07', '2012-08-07', 0),
            array(array(array('2012-08-01', '2012-08-10'), array('2012-08-13', '2012-08-16')), '2012-08-13', '2012-08-16', 0),
            array(array(array('2012-08-01', '2012-08-10'), array('2012-08-13', '2012-08-16')), '2012-08-13', '2012-08-18', 2),
            array(array(array('2012-08-01', '2012-08-10'), array('2012-08-13', '2012-08-16')), '2012-08-12', '2012-08-18', 3),

            array(array(array('2012-08-01', '2012-08-10'), array('2012-08-01', '2012-08-16')), '2012-07-30', '2012-08-14', 2),
            array(array(array('2012-08-01', '2012-08-10'), array('2012-08-01', '2012-08-16')), '2012-08-01', '2012-08-16', 0),
            array(array(array('2012-08-01', '2012-08-10'), array('2012-08-01', '2012-08-16')), '2012-08-01', '2012-08-10', 0),
            array(array(array('2012-08-01', '2012-08-10'), array('2012-08-01', '2012-08-16')), '2012-08-10', '2012-08-12', 0),
            array(array(array('2012-08-01', '2012-08-10'), array('2012-08-01', '2012-08-16')), '2012-08-10', '2012-08-16', 0),
            array(array(array('2012-08-01', '2012-08-10'), array('2012-08-01', '2012-08-16')), '2012-08-10', '2012-08-18', 2),
            array(array(array('2012-08-01', '2012-08-10'), array('2012-08-01', '2012-08-16')), '2012-08-11', '2012-08-11', 0),
            array(array(array('2012-08-01', '2012-08-10'), array('2012-08-01', '2012-08-16')), '2012-08-10', '2012-08-10', 0),
            array(array(array('2012-08-01', '2012-08-10'), array('2012-08-01', '2012-08-16')), '2012-08-07', '2012-08-07', 0),
            array(array(array('2012-08-01', '2012-08-10'), array('2012-08-01', '2012-08-16')), '2012-08-13', '2012-08-16', 0),
            array(array(array('2012-08-01', '2012-08-10'), array('2012-08-01', '2012-08-16')), '2012-08-13', '2012-08-18', 2),
            array(array(array('2012-08-01', '2012-08-10'), array('2012-08-01', '2012-08-16')), '2012-08-12', '2012-08-18', 2),
        );
    }

    private function getBannerZoneReferenceMock(array $dates)
    {
        $ref = new BannerZoneReference();
        $orders = array();
        foreach ($dates as $date) {
            $orders[] = $this->getOrderMock($date[0], $date[1]);
        }
        $ref->setOrders($orders);

        return $ref;
    }

    private function getOrderMock($from, $to)
    {
        $order = new Order();
        $order->setPaymentFrom(new \DateTime($from));
        $order->setPaymentTo(new \DateTime($to));

        return $order;
    }
}
