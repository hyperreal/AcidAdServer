<?php

namespace Hyper\AdsBundle\EventListener;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Hyper\AdsBundle\Entity\Advertisement;
use Hyper\AdsBundle\Entity\Announcement;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Templating\EngineInterface;
use Symfony\Component\Translation\TranslatorInterface;

class AnnouncementFlowEventListener implements EventSubscriber
{
    /** @var \Symfony\Component\Translation\TranslatorInterface */
    private $translator;
    /** @var \Symfony\Component\Templating\EngineInterface */
    private $templating;
    /** @var \Swift_Mailer */
    private $mailer;
    private $mailingPersonList;
    private $mailerFromEmail;
    private $mailerFromName;

    /** @var \Symfony\Component\DependencyInjection\ContainerInterface */
    private $container;

    public function __construct(
        ContainerInterface $container,
        array $mailingPersonList,
        $mailerFromEmail,
        $mailerFromName
    ) {
        $this->container = $container;
        $this->mailingPersonList = $mailingPersonList;
        $this->mailerFromEmail = $mailerFromEmail;
        $this->mailerFromName = $mailerFromName;
    }

    public function getSubscribedEvents()
    {
        return array(
            'postPersist',
            'postUpdate'
        );
    }

    public function postPersist(LifecycleEventArgs $args)
    {
        $this->mailing($args);
    }

    public function postUpdate(LifecycleEventArgs $args)
    {
        $this->mailing($args);
    }

    /**
     * @param LifecycleEventArgs $args
     */
    private function mailing(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();
        if (empty($this->mailingPersonList) || !($entity instanceof Announcement)) {
            return;
        }

        $this->setUpServices();
        $this->mailer->send($this->prepareMessage($entity));
    }

    /**
     * @param \Hyper\AdsBundle\Entity\Advertisement $entity
     * @return \Swift_Message
     */
    private function prepareMessage(Advertisement $entity)
    {
        $message = new \Swift_Message();
        $messageContent = $this->templating->render(
            'HyperAdsBundle:Mailing:postPersistAnnouncement.html.twig',
            array(
                'announcement' => $entity
            )
        );
        $message->setBody($messageContent, 'text/html', 'utf-8');
        $message->setFrom($this->mailerFromEmail, $this->mailerFromName);
        $message->setSubject(
            $this->translator->trans(
                'announcement.edited',
                array(
                    '%user%' => $entity->getAdvertiser()->getUsernameCanonical(),
                    '%title%' => $entity->getTitle()
                ),
                'HyperAdsBundle'
            )
        );

        foreach ($this->mailingPersonList as $person) {
            $message->addTo($person);
        }

        return $message;
    }

    private function setUpServices()
    {
        //this way, because of circular reference problem
        $this->translator = $this->container->get('translator');
        $this->templating = $this->container->get('templating');
        $this->mailer = $this->container->get('mailer');
    }
}