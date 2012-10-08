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
        return array();
    }

    /**
     * @Route("/show/{id}")
     * @Template()
     */
    public function showAction($id)
    {
        /** @var $em \Doctrine\ORM\EntityManager */
        $em = $this->get('doctrine.orm.entity_manager');
        /** @var $bannerRepository \Hyper\AdsBundle\Entity\BannerRepository */
        $bannerRepository = $em->getRepository('HyperAdsBundle:Banner');
        $zoneRepository = $em->getRepository('HyperAdsBundle:Zone');

        /** @var $zone \Hyper\AdsBundle\Entity\Zone */
        $zone = $zoneRepository->find($id);

        if (empty($zone)) {
            throw $this->createNotFoundException('Zone not found.');
        }

        $banner = $bannerRepository->getRandomBannerInZone($zone);

        if (empty($banner)) {
            throw $this->createNotFoundException('No banner found.');
        }

        return array(
            'banner' => $banner,
        );
    }

    /**
     * @Route("/head")
     */
    public function headAction()
    {
        $resp = new Response();

        $resp->headers->set('Content-type', 'text/javascript');
        $resp->headers->set('Cache-control', 'max-age=172800, public, must-revalidate');

        $resp->setContent(
            $this->renderView(
                'HyperAdsBundle:Default:head.js.twig',
                array(
                    'server' => $this->getRequest()->getHttpHost(),
                )
            )
        );

        return $resp;
    }
}
