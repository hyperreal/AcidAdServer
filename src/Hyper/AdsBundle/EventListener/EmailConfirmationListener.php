<?php

namespace Hyper\AdsBundle\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\Event\KernelEvent;
use FOS\UserBundle\Event\FormEvent;
use FOS\UserBundle\FOSUserEvents;

class EmailConfirmationListener implements EventSubscriberInterface
{
    private $session;
    private $save = false;

    public function __construct(SessionInterface $session)
    {
        $this->session = $session;
    }

    public static function getSubscribedEvents()
    {
        return array(
            KernelEvents::RESPONSE => 'saveSession' ,
            FOSUserEvents::REGISTRATION_SUCCESS => 'onRegistrationSuccess',
        );
    }

    public function saveSession(KernelEvent $event)
    {
        if ($this->save) {
            $this->session->save();
        }
    }

    public function onRegistrationSuccess(FormEvent $event)
    {
        $this->save = true;
    }
}
