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
        return $announcement; // :)
    }

    /**
     * @Route("/announcement/report", name="api_report")
     * @Method("POST")
     * @Api\Json()
     */
    public function reportAnnouncementAction()
    {
        $id = $this->getRequest()->request->get('id');

        if (!is_numeric($id) || $id < 1) {
            throw new BadRequestHttpException('Report ID must be a positive integer');
        }

        $announcement = $this->entityManager->getRepository('HyperAdsBundle:Announcement')->find($id);
        if (empty($announcement)) {
            throw $this->createNotFoundException('Given advertisement does not exist');
        }

        $report = new AdvertisementReport();
        $report->setAdvertisement($announcement);

        $this->entityManager->persist($report);
        $this->entityManager->flush();

        return array(
            's' => true,
            'm' => 'OK'
        );
    }

    /**
     * @Route("/occupancy/{year}-{month}", name="api_zones_occupancy", requirements={"year": "\d{4}", "month": "\d{2}"})
     * @Method("GET")
     * @Api\Json()
     */
    public function zonesOccupancyAction($year, $month)
    {
        /** @var $calendar \Hyper\AdsBundle\Helper\BannerZoneCalendar */
        $calendar = $this->get('hyper_ads.banner_zone_calendar');

        $date = mktime(0, 0, 0, ltrim($month, '0'), 1, $year);
        $from = new \DateTime(date('Y-m-d', $date));
        $to = new \DateTime(date('Y-m-t', $date));

        return $calendar->createOccupancyReport($from, $to);
    }

}
