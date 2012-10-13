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
    const MAIL_SENDER = 'czeslawfajka.hyperreal@gmail.com';

    protected function configure()
    {
        $this->setName('ads:notify:expiring-campaigns')
            ->setDescription('Notifies campaigns\' owners about expiring campaigns');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /** @var $campaignRepository \Hyper\AdsBundle\Entity\CampaignRepository */
        $campaignRepository = $this->getContainer()->get('doctrine.orm.entity_manager')->getRepository(
            'HyperAdsBundle:Campaign'
        );

        $expiringCampaigns = $campaignRepository->getExpiringCampaigns();

        if (empty($expiringCampaigns)) {
            $output->writeln('<info>No expiring campaigns found.</info>');
            return;
        }

        $expiringCampaignsOwners = $this->groupCampaignsByEmailField($expiringCampaigns);
        /** @var $mailer \Swift_Mailer */
        $mailer = $this->getContainer()->get('mailer');

        foreach ($expiringCampaignsOwners as $owner => $campaign) {
            /** @var $campaign \Hyper\AdsBundle\Entity\Campaign[] */
            $email = $this->composeEmail($owner, $campaign);
            $mailer->send($email);
        }
    }

    private function groupCampaignsByEmailField($expiringCampaigns)
    {
        $expiringCampaignsOwners = array();

        foreach ($expiringCampaigns as $campaign) {
            $email = $campaign->getAdvertiser()->getEmail();
            if (!isset($expiringCampaignsOwners[$email])) {
                $expiringCampaignsOwners[$email] = array();
            }
            $expiringCampaignsOwners[$email][] = $campaign;
        }

        return $expiringCampaignsOwners;
    }

    /**
     * @param       $owner
     * @param array $campaigns
     * @return \Swift_Message
     */
    private function composeEmail($owner, array $campaigns)
    {
        /** @var $translator \Symfony\Component\Translation\Translator */
        $translator = $this->getContainer()->get('translator');
        $message = \Swift_Message::newInstance()
            ->setSubject($translator->trans('mail.expiring.subject', array(), 'HyperAdsBundle'))
            ->setFrom(self::MAIL_SENDER)
            ->setTo($owner)
            ->setBody(
                $translator->trans(
                    'mail.expiring.content',
                    array('campaigns' => $campaigns, 'owner' => $owner),
                    'HyperAdsBundle'
                )
            );

        return $message;
    }

}
