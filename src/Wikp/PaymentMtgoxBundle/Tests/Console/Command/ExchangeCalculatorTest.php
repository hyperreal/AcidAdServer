<?php

namespace Wikp\PaymentMtgoxBundle\Tests\Console\Command;

use Wikp\PaymentMtgoxBundle\Console\Command\ExchangeCalculator;
use Symfony\Component\DependencyInjection\Container;
use Wikp\PaymentMtgoxBundle\Mtgox\Exchange;

class ExchangeCalculatorTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Wikp\PaymentMtgoxBundle\Console\Command\ExchangeCalculator */
    private $command;

    public function setUp()
    {
        $this->command = new ExchangeCalculator();

    }

    public function testValidParams()
    {
        $this->setContainer($this->getExchangeMock(false, 10, 'USD', 1000));
        $this->command->run($this->getInputMock(), $this->getOutputMock());
    }

    private function getOutputMock()
    {
        $mock = $this->getMockBuilder('Symfony\Component\Console\Output\OutputInterface')
            ->getMockForAbstractClass();

        return $mock;
    }

    private function getInputMock($fromBtc = true, $currency = 'BTC', $amount = 10)
    {
        $mock = $this->getMockBuilder('Symfony\Component\Console\Input\InputInterface')
            ->getMockForAbstractClass();

        $mock->expects($this->at(1))
            ->method('getOption')
            ->with($this->equalTo('from-btc'))
            ->will($this->returnValue($fromBtc));

        $mock->expects($this->at(2))
            ->method('getOption')
            ->with($this->equalTo('currency'))
            ->will($this->returnValue($currency));

        $mock->expects($this->at(1))
            ->method('getArgument')
            ->with($this->equalTo('amount'))
            ->will($this->returnValue($amount));

        return $mock;
    }

    private function setContainer(Exchange $mock)
    {
        $container = new Container();
        $container->set('wikp_payment_mtgox.exchange', $mock);

        $this->command->setContainer($container);
    }

    private function getExchangeMock($fromBtc, $amount, $currency, $expectedAmount)
    {
        $mock = $this->getMockBuilder('Wikp\PaymentMtgoxBundle\Mtgox\Exchange')
            ->disableOriginalConstructor()
            ->setMethods(array('convertFromBitcoins', 'convertToBitcoins'))
            ->getMock();

        if ($fromBtc) {
            $mock->expects($this->once())
                ->method('convertFromBitcoins')
                ->with($this->equalTo($amount), $this->equalTo($currency))
                ->will($this->returnValue($expectedAmount));
            $mock->expects($this->never())
                ->method('convertToBitcoins');
        } else {
            $mock->expects($this->once())
                ->method('convertToBitcoins')
                ->with($this->equalTo($amount), $this->equalTo($currency))
                ->will($this->returnValue($expectedAmount));
            $mock->expects($this->never())
                ->method('convertFromBitcoins');
        }

        return $mock;
    }
}
