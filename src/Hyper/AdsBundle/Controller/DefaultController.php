<?php

namespace Hyper\AdsBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Response;
use Hyper\AdsBundle\Entity\Zone;

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
     * @Route("/tutorial", name="acid_tutorial")
     * @Template()
     */
    public function tutorialAction()
    {
        return array();
    }

    /**
     * @Route("/frame/{id}", name="hyper_ads_default_frame")
     * @Template()
     */
    public function frameAction($id)
    {
        /** @var $em \Doctrine\ORM\EntityManager */
        $em = $this->get('doctrine.orm.entity_manager');
        /** @var $zone \Hyper\AdsBundle\Entity\Zone */
        $zone = $em->getRepository('HyperAdsBundle:Zone')->find($id);

        if (empty($zone)) {
            throw $this->createNotFoundException('Zone not found.');
        }

        /** @var $banner \Hyper\AdsBundle\Entity\AdvertisementRepository */
        $banner = $em->getRepository('HyperAdsBundle:Banner')->getRandomBannerInZone($zone);

        if (empty($banner)) {
            throw $this->createNotFoundException('No banner found.');
        }

        /** @var $banner \Hyper\AdsBundle\Entity\BannerZoneReference */
        $reference = $banner->getReferenceInZone($id);

        /** @var $statsCollector \Hyper\AdsBundle\Helper\StatsCollector */
        $statsCollector = $this->get('stats_collector');
        $statsCollector->collectView($reference);

        return array(
            'banner' => $banner,
            'zone' => $zone
        );
    }

    /**
     * @Route("/click/{zoneId}/{bannerId}", name="banner_click")
     * @todo prevention from bots' clicks
     */
    public function clickAction($zoneId, $bannerId)
    {
        /** @var $bannerRepository \Hyper\AdsBundle\Entity\BannerRepository */
        $bannerRepository = $this->get('doctrine.orm.entity_manager')->getRepository('HyperAdsBundle:Banner');

        $reference = $bannerRepository->getBannerReference($bannerId, $zoneId);

        if (empty($reference)) {
            throw $this->createNotFoundException('Banner is not present in given zone');
        }

        /** @var $statsCollector \Hyper\AdsBundle\Helper\StatsCollector */
        $statsCollector = $this->get('stats_collector');
        $statsCollector->collectClick($reference);

        return $this->redirect($reference->getBanner()->getUrl());
    }

    /**
     * @Route("/head", name="default_head")
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

    /**
     * @Route("/zones-info", name="default_zones_info")
     * @Template()
     */
    public function zonesInfoAction()
    {
        return array(
            'pages' => $this
                ->get('doctrine.orm.entity_manager')
                ->getRepository('HyperAdsBundle:Zone')
                ->getPagesWithActiveZones()
        );
    }

    /**
     * @Route("/demo")
     * @Template()
     */
    public function demoAction()
    {
        return array();
    }

    /**
     * @Route("/rules", name="default_rules")
     * @Template()
     */
    public function rulesAction()
    {
        return array();
    }

}
