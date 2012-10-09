<?php

namespace Hyper\AdsBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Hyper\AdsBundle\Entity\Banner;
use Hyper\AdsBundle\Entity\BannerZoneReference;

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
        /** @var $tr \Symfony\Component\Translation\Translator */
        $tr = $this->get('translator');

        /** @var $bannerZoneRepository \Hyper\AdsBundle\Entity\BannerZoneReferenceRepository */
        $bannerZoneRepository   = $em->getRepository('HyperAdsBundle:BannerZoneReference');
        /** @var $bannerRepository \Hyper\AdsBundle\Entity\BannerRepository */
        $bannerRepository       = $em->getRepository('HyperAdsBundle:Banner');
        $zoneRepository         = $em->getRepository('HyperAdsBundle:Zone');

        $zone = $zoneRepository->find($zoneId);
        if (empty($zone)) {
            throw $this->createNotFoundException($tr->trans('zone.not.exists', array(), 'HyperAdsBundle'));
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
        /** @var $tr \Symfony\Component\Translation\Translator */
        $tr = $this->get('translator');

        $newBannerIds   = $request->get('newBanners');
        $probabilities  = $request->get('probability');
        $zoneId         = $request->get('zoneId');

        if (!is_array($newBannerIds)) {
            throw new \Exception('invalid banner ID\'s');
        }

        /** @var $em \Doctrine\ORM\EntityManager */
        $em = $this->get('doctrine.orm.entity_manager');

        $zonesRepository        = $em->getRepository('HyperAdsBundle:Zone');
        /** @var $bannersRepository \Hyper\AdsBundle\Entity\BannerRepository */
        $bannersRepository      = $em->getRepository('HyperAdsBundle:Banner');
        /** @var $bannersRefRepository \Hyper\AdsBundle\Entity\BannerZoneReferenceRepository */
        $bannersRefRepository    = $em->getRepository('HyperAdsBundle:BannerZoneReference');

        /** @var $zone \Hyper\AdsBundle\Entity\Zone */
        $zone = $zonesRepository->find($zoneId);
        if (empty($zone)) {
            throw $this->createNotFoundException($tr->trans('zone.not.exists', array(), 'HyperAdsBundle'));
        }

        /** @var $banners \Hyper\AdsBundle\Entity\Banner[] */
        $banners = $bannersRepository->findBy(array('id' => $newBannerIds));
        $bannersRefRepository->updateReferences($zone, $banners, $probabilities);

        return $this->redirect($this->generateUrl('admin_zone_show', array('id' => $zoneId)));
    }

}
