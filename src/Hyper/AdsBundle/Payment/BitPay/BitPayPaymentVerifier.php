<?php

namespace Hyper\AdsBundle\Payment\BitPay;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class BitPayPaymentVerifier extends ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName('bitpay:verify')
            ->setDescription(
                'Verifies BitPay payments. Connects to bitpay.com API and set proper values to the database'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {

    }


} 