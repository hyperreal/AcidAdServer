<?php

namespace Hyper\AdsBundle\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Class that listens to the KernelEvents::RESPONSE and saves session, when it has been started already.
 * We need to do this because there is a bug in PHP:
 * @see https://bugs.php.net/bug.php?id=63963
 */
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
