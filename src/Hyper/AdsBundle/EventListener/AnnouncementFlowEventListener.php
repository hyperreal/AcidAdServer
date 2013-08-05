<?php

namespace Hyper\AdsBundle\EventListener;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Hyper\AdsBundle\Entity\Advertisement;
use Hyper\AdsBundle\Entity\Advertiser;
use Hyper\AdsBundle\Entity\Announcement;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Templating\EngineInterface;
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\Security\Core\SecurityContext;

class AnnouncementFlowEventListener implements EventSubscriber
{
    /** @var \Symfony\Component\Translation\TranslatorInterface */
    private $translator;
    /** @var \Symfony\Component\Templating\EngineInterface */
    private $templating;
    /** @var \Swift_Mailer */
    private $mailer;
    /** @var  \Symfony\Component\Security\Core\SecurityContext */
    private $securityContext;

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
            'postUpdate',
            'preUpdate',
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

    public function preUpdate(LifecycleEventArgs $args)
    {
        $this->setUpServices();
        $this->markAsModified($args);
    }

    private function markAsModified(PreUpdateEventArgs $args)
    {
        $entity = $args->getEntity();
        $securityToken = $this->securityContext->getToken();
        if (!($entity instanceof Advertisement) || null == $securityToken) {
            return;
        }

        $user = $securityToken->getUser();
        if (($args->hasChangedField('description') || $args->hasChangedField('title'))
            && $user instanceof Advertiser
            && $user->getId() == $entity->getAdvertiser()->getId()
        ) {
            $entity->markAsModified();
            $args->getEntityManager()->getUnitOfWork()->computeChangeSet(
                $args->getEntityManager()->getClassMetadata(get_class($entity)),
                $entity
            );
        }
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
        $this->securityContext = $this->container->get('security.context');
    }
}