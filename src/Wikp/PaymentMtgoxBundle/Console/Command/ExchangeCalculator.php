<?php

namespace Wikp\PaymentMtgoxBundle\Console\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Wikp\PaymentMtgoxBundle\Exception\InvalidArgumentException;
use Wikp\PaymentMtgoxBundle\Exception\CurrencyException;

class ExchangeCalculator extends ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName('wikp:mtgox:exchange')
            ->setDescription('Converts amounts between currencies')
            ->addOption('from-btc', 'b', InputOption::VALUE_NONE, 'Converts from BTC to currency')
            ->addOption('currency', 'c', InputOption::VALUE_REQUIRED, 'Currency to/from convert', 'EUR')
            ->addArgument('amount', InputArgument::REQUIRED, 'Amount to convert');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $convertFromBtc = $input->getOption('from-btc');
        $currency = strtoupper($input->getOption('currency'));
        $amount = $input->getArgument('amount');

        if ($convertFromBtc) {
            $from = 'BTC';
            $to = $currency;
        } else {
            $from = $currency;
            $to = 'BTC';
        }

        /** @var $exchange \Wikp\PaymentMtgoxBundle\Mtgox\Exchange */
        $exchange = $this->getContainer()->get('wikp_payment_mtgox.exchange');

        try {
            $convertedAmount = $convertFromBtc ?
                $exchange->convertFromBitcoins($amount, $currency) :
                $exchange->convertToBitcoins($amount, $currency);
            $output->writeln("$amount $from = <info>$convertedAmount $to</info>");
        } catch (InvalidArgumentException $e) {
            $output->writeln('<error>' . $e->getMessage() . '</error>');
        } catch (CurrencyException $e) {
            $output->writeln('<error>' . $e->getMessage() . '</error>');
        }
    }
}
