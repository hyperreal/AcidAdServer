<?php

namespace Hyper\AdsBundle\Console\Command;

use Hyper\AdsBundle\Entity\Advertisement;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ExpireAnnouncements extends ContainerAwareCommand
{
    /** @var \DateTime */
    private $numberOfDaysAgo;
    /** @var boolean */
    private $verbose;
    /** @var \Doctrine\ORM\EntityManager */
    private $entityManager;
    /** @var $repository \Hyper\AdsBundle\Entity\AdvertisementRepository */
    private $repository;

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
        $this->setUp($input);
        $announcements = $this->repository->getExpiredAnnouncements($this->numberOfDaysAgo);

        if (empty($announcements)) {
            $this->verboseWrite($output, 'There are no expired announcements');
            return;
        }

        foreach ($announcements as $announcement) {
            $this->markAsExpiredAndPersist($output, $announcement);
        }

        $this->entityManager->flush();
    }

    private function verboseWrite(OutputInterface $output, $message)
    {
        if ($this->verbose) {
            $output->writeln($message);
        }
    }

    private function markAsExpiredAndPersist(OutputInterface $output, Advertisement $announcement)
    {
        $announcement->setExpired(true);
        $this->entityManager->persist($announcement);
        $this->verboseWrite(
            $output,
            sprintf(
                'Announcement with id <info>%d</info> (<comment>%s</comment>) marked as expired',
                $announcement->getId(),
                $announcement->getTitle()
            )
        );
    }

    private function setUp(InputInterface $input)
    {
        $this->verbose = $input->getOption('verbose');
        $this->entityManager = $this->getContainer()->get('doctrine.orm.entity_manager');

        $this->numberOfDaysAgo = $numberOfDaysAgo = new \DateTime(
            sprintf('now - %d days', $this->getContainer()->getParameter('announcement_expire_days'))
        );

        $this->repository = $this->entityManager->getRepository('HyperAdsBundle:Advertisement');
    }
}