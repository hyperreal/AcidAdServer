<?php

namespace Hyper\AdsBundle\Console\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class RemoveExpiredOrders extends ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName('acid:task:remove-expired-orders')
            ->setDescription('Removes orders that have not been paid within specific amount of time')
            ->addOption(
                'time',
                null,
                InputOption::VALUE_REQUIRED,
                'Time in minutes. Non-paid orders created earlier than NOW - {time} will be removed.',
                1440
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $time = $input->getOption('time');
        if (!is_numeric($time) || $time < 1) {
            $output->writeln('<error>Time should be a positive integer.</error>');
            return;
        }

        $count = $this->getContainer()
            ->get('doctrine.orm.entity_manager')
            ->getRepository('HyperAdsBundle:Order')
            ->removeExpiredOrders(intval($time));

        $output->writeln("<info>$count orders removed</info>");
    }
}