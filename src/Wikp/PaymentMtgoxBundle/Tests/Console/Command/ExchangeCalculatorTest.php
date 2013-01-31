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
        $this->setContainer($this->getExchangeMock(true, 10, 'EUR', 1000));
        $this->command->run($this->getInputMock(), $this->getOutputMock());
    }

    private function getOutputMock()
    {
        $mock = $this->getMockBuilder('Symfony\Component\Console\Output\OutputInterface')
            ->getMockForAbstractClass();

        $mock->expects($this->any())
            ->method('writeln')
            ->with("10 BTC = <info>1000 EUR</info>");

        return $mock;
    }

    private function getInputMock($fromBtc = true, $currency = 'EUR', $amount = 10)
    {
        $mock = $this->getMockBuilder('Symfony\Component\Console\Input\InputInterface')
            ->setMethods(array('getOption', 'getArgument'))
            ->getMockForAbstractClass();

        $mock->expects($this->any())
            ->method('getOption')
            ->will(
                $this->returnCallback(
                    function ($name) use ($currency, $fromBtc) {
                        if ('from-btc' == $name) {
                            return $fromBtc;
                        } elseif ('currency' == $name) {
                            return $currency;
                        }
                    }
                )
            );

        $mock->expects($this->any())
            ->method('getArgument')
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
                //->with($this->equalTo($amount), $this->equalTo($currency))
                ->will($this->returnValue($expectedAmount));
            $mock->expects($this->never())
                ->method('convertFromBitcoins');
        }

        return $mock;
    }
}
