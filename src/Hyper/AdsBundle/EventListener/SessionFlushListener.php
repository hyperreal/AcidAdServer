<?php

namespace Hyper\AdsBundle\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpKernel\KernelEvents;

class SessionFlushListener implements EventSubscriberInterface
{
    private $session;

    public function __construct(SessionInterface $session)
    {
        $this->session = $session;
    }

    public static function getSubscribedEvents()
    {
        return array(
            KernelEvents::RESPONSE => 'saveSession' ,
        );
    }

    public function saveSession()
    {
        if ($this->session->isStarted()) {
            $this->session->save();
        }
    }
}
