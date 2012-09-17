<?php

namespace Hyper\AdsBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Hyper\AdsBundle\Entity\Banner;
use Hyper\AdsBundle\Form\BannerType;

class BannerConfigController extends Controller
{

    /**
     * @Route("/", name="admin_banner_config")
     */
    public function indexAction()
    {
        return $this->redirect($this->generateUrl('_welcome'));
    }

    /**
     * @Route("/by-zone/{zoneId}", name="banner_config_by_zone")
     * @Template()
     */
    public function byZoneAction($zoneId)
    {
        /** @var $em \Doctrine\ORM\EntityManager */
        $em = $this->get('doctrine.orm.entity_manager');
        /** @var $tr \Symfony\Component\Translation\Translator */
        $tr = $this->get('translator');

        $bannerZoneRepository   = $em->getRepository('HyperAdsBundle:BannerZoneReference');
        $zoneRepository         = $em->getRepository('HyperAdsBundle:Zone');
        $bannerRepository       = $em->getRepository('HyperAdsBundle:Banner');

        $zone = $zoneRepository->find($zoneId);
        if (empty($zone)) {
            throw $this->createNotFoundException($tr->trans('zone.not.exists', array(), 'HyperAdsBundle'));
        }

        $allBanners = $bannerRepository->findAll();
        $bannersReferences = $bannerZoneRepository->findBy(array(
            'zone' => $zone,
        ));

        return array(
            'bannersReferences' => $bannersReferences,
            'allBanners'        => $allBanners,
            'zone'              => $zone,
        );
    }

    /**
     * @Route("/by-banner/{bannerId}", name="banner_config_by_banner")
     * @Template()
     */
    public function byBannerAction($bannerId)
    {

    }



}
