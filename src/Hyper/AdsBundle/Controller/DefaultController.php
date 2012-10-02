<?php

namespace Hyper\AdsBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Response;

class DefaultController extends Controller
{
    /**
     * @Route("/")
     * @Template()
     */
    public function indexAction()
    {
        return array('name' => 'XXX');
    }

    /**
     * @Route("/show/{id}")
     */
    public function showAction($zoneId)
    {
        return array();
    }

    /**
     * @Route("/head")
     */
    public function headAction()
    {
        $resp = new Response();

        $resp->headers->set('Content-type', 'text/javascript');
        $resp->headers->set('Cache-control', 'max-age=172800, public, must-revalidate');

        $resp->setContent($this->renderView('HyperAdsBundle:Default:head.js.twig', array(
            'server' => $this->getRequest()->getHttpHost(),
        )));

        return $resp;
    }
}
