<?php

namespace Wikp\PaymentMtgoxBundle\Console\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Wikp\PaymentMtgoxBundle\Entity\Currency;
use Wikp\PaymentMtgoxBundle\Plugin\MtgoxPaymentPlugin;
use Wikp\PaymentMtgoxBundle\Exception\InvalidArgumentException;

class RetrieveCurrentPrices extends ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName('wikp:mtgox:retrieve-prices')
            ->setDescription(
                'Retrieve current prices from MtGox API and store it in database. If no argument is'
                . ' presented, prices of all currencies will be downloaded'
            )
            ->addArgument('currencyCode', InputArgument::OPTIONAL, 'currency which prices should be downloaded');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $currencyCode = strtoupper($input->getArgument('currencyCode'));
        if ($currencyCode && !in_array($currencyCode, MtgoxPaymentPlugin::getValidMtgoxCurrencyCodes())) {
            throw new InvalidArgumentException('You provide invalid currency code');
        }

        if (!$currencyCode) {
            $this->retrieveAllCurrenciesPrices($output);
        } else {
            $this->retrieveCurrencyPrices($currencyCode, $output);
        }
    }

    protected function retrieveAllCurrenciesPrices(OutputInterface $output)
    {
        foreach (MtgoxPaymentPlugin::getValidMtgoxCurrencyCodes() as $currencyCode) {
            $this->retrieveCurrencyPrices($currencyCode, $output);
        }
    }

    protected function retrieveCurrencyPrices($currencyCode, OutputInterface $output)
    {
        $output->writeln('');
        $output->write('<info>Retrieve prices for </info>');
        $output->writeln('<comment>' . $currencyCode . '</comment><info>...</info>');

        $retriever = $this->getPricesRetriever();
        $currency = $retriever->updateCurrencyPrices($this->getCurrencyObject($currencyCode));

        $output->writeln('<info>Prices retrieved</info>');
        $output->writeln('<info>Buy price: </info><comment>' . $currency->getBuyPrice() . '</comment>');
        $output->writeln('<info>Sell price: </info><comment>' . $currency->getSellPrice() . '</comment>');
        $output->writeln('<info>Prices stored in database</info>');
    }

    /**
     * @param $currencyCode
     *
     * @return \Wikp\PaymentMtgoxBundle\Entity\Currency
     */
    protected function getCurrencyObject($currencyCode)
    {
        $object = $this
            ->getContainer()
            ->get('doctrine.orm.entity_manager')
            ->getRepository('WikpPaymentMtgoxBundle:Currency')
            ->findOneBy(
                array(
                    'code' => $currencyCode
                )
            );

        if (empty($object)) {
            $object = new Currency();
            $object->setCode($currencyCode);
            $object->setName($currencyCode);
        }

        return $object;
    }

    /**
     * @return \Wikp\PaymentMtgoxBundle\Mtgox\CurrentPricesRetriever
     */
    protected function getPricesRetriever()
    {
        return $this->getContainer()->get('wikp_payment_mtgox.current_prices_retriever');
    }
}
