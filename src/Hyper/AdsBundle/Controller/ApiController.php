<?php

namespace Hyper\AdsBundle\Controller;

use Hyper\AdsBundle\Entity\Announcement;
use Hyper\AdsBundle\Api;
use Hyper\AdsBundle\Entity\AdvertisementReport;
use JMS\DiExtraBundle\Annotation\Inject;
use Symfony\Component\HttpFoundation\Response;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class ApiController extends RestController
{
    /**
     * @Inject("doctrine.orm.entity_manager")
     * @var \Doctrine\ORM\EntityManager
     */
    private $entityManager;

    /**
     * @Route("/announcement", name="api_announcement_list")
     * @Method("GET")
     * @Api\Json()
     */
    public function getAnnouncementListAction()
    {
        return $this->entityManager->getRepository('HyperAdsBundle:Announcement')->getLastAnnouncementsForApi();
    }

    /**
     * @Route("/announcement/{announcement}", name="api_announcement", requirements={"announcement": "\d+"})
     * @Method("GET")
     * @Api\Json(full=true)
     */
    public function getAnnouncementAction(Announcement $announcement)
    {
        return $this->getJsonResponse(
            $this->get('hyper_ads.entity_serializer')->toJson($announcement, true)
        );
    }

    /**
     * @Route("/announcement/report", name="api_report")
     * @Method("POST")
     * @Api\Json()
     */
    public function reportAnnouncementAction()
    {
        $id = $this->getRequest()->request->get('report');

        if (!is_numeric($id) || $id < 1) {
            throw new BadRequestHttpException('Report ID must be a positive integer');
        }

        $announcement = $this->entityManager->getRepository('HyperAdsBundle:Announcement')->find($id);
        if (empty($announcement)) {
            throw $this->createNotFoundException('Given advertisement does not exist');
        }

        $report = new AdvertisementReport();
        $report->setAdvertisement($announcement);

        $em->persist($report);
        $em->flush();

        return $this->getJsonResponse(
            array(
                's' => true,
                'm' => 'OK'
            )
        );
    }

    private function getInvalidTokenResponse()
    {
        return $this->getJsonErrorResponse('Invalid token', 401);
    }

    private function getJsonErrorResponse($message, $statusCode)
    {
        return $this->getJsonResponse(
            array(
                's' => false,
                'm' => $message
            ),
            $statusCode
        );
    }

    private function getJsonResponse($content, $statusCode = 200)
    {
        return new Response(
            json_encode($content),
            $statusCode,
            array(
                'Content-type' => 'application/json; charset=utf-8',
            )
        );
    }
}
