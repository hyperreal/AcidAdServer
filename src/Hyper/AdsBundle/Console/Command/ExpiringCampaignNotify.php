<?php
/**
 * @author fajka <fajka@hyperreal.info>
 * @see    https://github.com/fajka
 */

namespace Hyper\AdsBundle\Console\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ExpiringCampaignNotify extends ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName('ads:notify:expiring-campaigns')
            ->setDescription('Notifies campaigns\' owners about expiring campaigns');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('<error>This functionality is not implemented yet.</error>');
    }

}
