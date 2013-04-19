<?php

namespace Hyper\AdsBundle\Controller\Admin;

use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Hyper\AdsBundle\Entity\Banner;
use Hyper\AdsBundle\Entity\BannerZoneReference;
use Hyper\AdsBundle\Helper\ReferencesUpdater;
use Hyper\AdsBundle\Controller\Controller;

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
     * @Route("/zone/{zoneId}", name="banner_config_zone")
     * @Template()
     */
    public function zoneAction($zoneId)
    {
        /** @var $em \Doctrine\ORM\EntityManager */
        $em = $this->get('doctrine.orm.entity_manager');

        /** @var $bannerZoneRepository \Hyper\AdsBundle\Entity\BannerZoneReferenceRepository */
        $bannerZoneRepository   = $em->getRepository('HyperAdsBundle:BannerZoneReference');
        /** @var $bannerRepository \Hyper\AdsBundle\Entity\AdvertisementRepository */
        $bannerRepository       = $em->getRepository('HyperAdsBundle:Banner');
        $zoneRepository         = $em->getRepository('HyperAdsBundle:Zone');

        $zone = $zoneRepository->find($zoneId);
        if (empty($zone)) {
            throw $this->createNotFoundException($this->trans('zone.not.exists'));
        }

        $allBanners = $bannerRepository->getAllActiveBanners();
        $bannersReferences = $bannerZoneRepository->getBannerReferencesByZone($zone);

        $usedBannerIds = array();
        foreach ($bannersReferences as $bannerReference) {
            $usedBannerIds[] = $bannerReference->getBanner()->getId();
        }

        return array(
            'bannersReferences' => $bannersReferences,
            'allBanners'        => $allBanners,
            'zone'              => $zone,
            'usedBannerIds'     => $usedBannerIds,
        );
    }

    /**
     * @Route("/save", name="banner_config_save")
     * @Method("POST")
     * @Template()
     */
    public function saveAction(Request $request)
    {
        $fixedByAdminSpec = $request->get('newBanners');
        $probabilities = $request->get('probability');
        $zoneId = $request->get('zoneId');

        if (empty($fixedByAdminSpec)) {
            $fixedByAdminSpec = array();
        }

        if (!is_array($fixedByAdminSpec)) {
            throw new \Exception('invalid banner ID\'s');
        }

        /** @var $em \Doctrine\ORM\EntityManager */
        $em = $this->get('doctrine.orm.entity_manager');

        $zonesRepository        = $em->getRepository('HyperAdsBundle:Zone');
        /** @var $bannersRepository \Hyper\AdsBundle\Entity\AdvertisementRepository */
        $bannersRepository      = $em->getRepository('HyperAdsBundle:Banner');

        /** @var $zone \Hyper\AdsBundle\Entity\Zone */
        $zone = $zonesRepository->find($zoneId);
        if (empty($zone)) {
            throw $this->createNotFoundException($this->trans('zone.not.exists'));
        }

        $banners = array();

        /** @var $banners \Hyper\AdsBundle\Entity\Banner[] */
        if (!empty($fixedByAdminSpec)) {
            $banners = $bannersRepository->findBy(array('id' => array_keys($fixedByAdminSpec)));
        }

        $referenceUpdater = new ReferencesUpdater($em);
        $referenceUpdater->setZone($zone);
        $referenceUpdater->setProbabilities($probabilities);
        $referenceUpdater->setBanners($banners);
        $referenceUpdater->setFixedByAdminSpecification($fixedByAdminSpec);
        $referenceUpdater->updateReferences();

        $this->get('session')->setFlash('success', $this->trans('banners.configs.are.saved'));

        return $this->redirect($this->generateUrl('admin_zone_show', array('id' => $zoneId)));
    }
}
