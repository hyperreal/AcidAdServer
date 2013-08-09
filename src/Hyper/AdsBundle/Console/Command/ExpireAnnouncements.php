<?php

namespace Hyper\AdsBundle\Console\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ExpireAnnouncements extends ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName('acid:task:expire-announcements')
            ->setDescription(
                'Mark announcements as expired if they are not edited for at least number of days defined'
                . ' in configuration'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $numberOfDaysAgo = new \DateTime(
            sprintf('now - %d days', $this->getContainer()->getParameter('announcement_expire_days'))
        );

        /** @var $em \Doctrine\ORM\EntityManager */
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');
        /** @var $repository \Hyper\AdsBundle\Entity\AdvertisementRepository */
        $repository = $em->getRepository('HyperAdsBundle:Advertisement');
        $announcements = $repository->getExpiredAnnouncements($numberOfDaysAgo);
        $verbose = $input->getOption('verbose');

        if ($verbose && empty($announcements)) {
            $output->writeln('There are no expired announcements');
            return;
        }

        foreach ($announcements as $announcement) {
            /** @var $announcement \Hyper\AdsBundle\Entity\Announcement */
            $announcement->setExpired(true);
            $em->persist($announcement);
            if ($verbose) {
                $output->writeln(
                    sprintf(
                        'Announcement with id <info>%d</info> (<comment>%s</comment>) marked as expired',
                        $announcement->getId(),
                        $announcement->getTitle()
                    )
                );
            }
        }
        $em->flush();
    }
}