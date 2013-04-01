<?php

namespace Hyper\AdsBundle\Controller;

use Hyper\AdsBundle\Entity\Announcement;
use Symfony\Component\HttpFoundation\Response;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;

class ApiController extends Controller
{
    /**
     * @Route("/announcement", name="api_announcement_list")
     * @Method("GET")
     */
    public function getAnnouncementListAction()
    {
        /** @var $announcementRepository \Hyper\AdsBundle\Entity\AdvertisementRepository */
        $announcementRepository = $this->getDoctrine()->getManager()->getRepository('HyperAdsBundle:Announcement');

        return $this->getJsonResponse(
            $this
                ->get('hyper_ads.entity_serializer')
                ->toJsonArray(
                    $announcementRepository->getLastAnnouncementsForApi()
                )
        );
    }

    /**
     * @Route("/announcement/{announcement}", name="api_announcement")
     * @Method("GET")
     */
    public function getAnnouncementAction(Announcement $announcement)
    {
        return $this->getJsonResponse(
            $this->get('hyper_ads.entity_serializer')->toJson($announcement, true)
        );
    }

    private function getJsonResponse($content)
    {
        return new Response(
            json_encode($content),
            200,
            array(
                'Content-type' => 'application/json; charset=utf-8',
            )
        );
    }
}
