<?php

namespace Hyper\AdsBundle\Controller\Admin;

use Hyper\AdsBundle\Controller\Controller;
use Hyper\AdsBundle\Entity\Announcement;
use JMS\DiExtraBundle\Annotation as DI;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Request;

class AnnouncementController extends Controller
{
    /**
     * @DI\Inject("doctrine.orm.entity_manager")
     * @var \Doctrine\ORM\EntityManager
     */
    private $entityManager;

    /**
     * @Route("/", name="admin_announcement_index")
     * @Template()
     */
    public function indexAction()
    {
        return array(
            'announcements' => $this->entityManager->getRepository('HyperAdsBundle:Announcement')->findAll()
        );
    }

    /**
     * @Route("/{announcement}", name="user_announcement_show")
     * @Template()
     */
    public function showAction(Announcement $announcement)
    {
        return array(
            'announcement' => $announcement
        );
    }

    /**
     * @Route("/add", name="user_announcement_new")
     * @Template()
     */
    public function addAction()
    {

    }

    /**
     * @Route("/save", name="user_announcement_create")
     * @Template("HyperAdsBundle:Admin:Announcement:add.html.twig")
     * @Method("POST")
     */
    public function createAction(Request $request)
    {

    }

    /**
     * @Route("/{announcement}/edit", name="user_announcement_edit")
     * @Template()
     *
     * @param Request $request
     * @param Announcement $announcement
     */
    public function editAction(Request $request, Announcement $announcement)
    {

    }

    /**
     * @Route("/{announcement}/save", name="user_announcement_save")
     * @Method("POST")
     * @Template("HyperAdsBundle:Announcement:edit")
     */
    public function saveAction(Request $request, Announcement $announcement)
    {

    }

    private function deleteAnnouncement(Announcement $announcement)
    {

    }

    private function updateAnnouncement(Announcement $announcement)
    {

    }

}