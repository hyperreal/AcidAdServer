<?php

namespace Hyper\AdsBundle\Controller\Admin;

use Hyper\AdsBundle\Controller\Controller;
use Hyper\AdsBundle\Entity\Announcement;
use Hyper\AdsBundle\Form\AnnouncementFullType;
use JMS\DiExtraBundle\Annotation as DI;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

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
     * @Route("/{announcement}", name="admin_announcement_show")
     * @Template()
     */
    public function showAction(Announcement $announcement)
    {
        return array(
            'announcement' => $announcement
        );
    }

    /**
     * @Route("/add", name="admin_announcement_new")
     * @Template()
     */
    public function addAction()
    {
        return array(
            'form' => $this->createForm(new AnnouncementFullType(), new Announcement())->createView(),
        );
    }

    /**
     * @Route("/save", name="user_announcement_create")
     * @Template("HyperAdsBundle:Admin:Announcement/add.html.twig")
     * @Method("POST")
     */
    public function createAction(Request $request)
    {
        $announcement = new Announcement();
        $form = $this->createForm(new AnnouncementFullType(), $announcement);
        $form->bind($request);

        if ($form->isValid()) {
            $this->entityManager->persist($announcement);
            $this->entityManager->flush($announcement);

            return $this->redirect($this->generateUrl('admin_announcement_index'));
        }

        return array(
            'form' => $form->createView(),
        );
    }

    /**
     * @Route("/{announcement}/edit", name="admin_announcement_edit")
     * @Template()
     */
    public function editAction(Announcement $announcement)
    {
        return array(
            'form' => $this->createForm(new AnnouncementFullType(), $announcement)->createView(),
            'announcement' => $announcement
        );
    }

    /**
     * @Route("/{announcement}/save", name="admin_announcement_save")
     * @Method("POST")
     * @Template("HyperAdsBundle:Admin:Announcement/edit.html.twig")
     */
    public function saveAction(Request $request, Announcement $announcement)
    {
        $action = $request->get('action');
        $this->checkFormAction($action);
        $request->request->remove('action');
        $form = $this->createForm(new AnnouncementFullType(), $announcement);
        $form->bind($request);

        if ($form->isValid()) {
            $this->persistOrRemoveAnnouncement($announcement, $action);

            return $this->redirect($this->generateUrl('admin_announcement_index'));
        }

        return array(
            'form' => $form->createView(),
            'announcement' => $announcement
        );
    }

    private function persistOrRemoveAnnouncement(Announcement $announcement, $action)
    {
        if ('update' == $action) {
            $this->updateAnnouncement($announcement);
            $this->get('session')->getFlashBag()->add('success', $this->trans('announcement.updated'));
        } else {
            $this->deleteAnnouncement($announcement);
            $this->get('session')->getFlashBag()->add('success', $this->trans('announcement.removed'));
        }
    }

    private function deleteAnnouncement(Announcement $announcement)
    {
        $this->entityManager->remove($announcement);
        $this->entityManager->flush();
    }

    private function updateAnnouncement(Announcement $announcement)
    {
        $this->entityManager->persist($announcement);
        $this->entityManager->flush();
    }

    /**
     * @param $action
     * @throws \Symfony\Component\HttpKernel\Exception\BadRequestHttpException
     */
    private function checkFormAction($action)
    {
        if (!in_array($action, array('update', 'delete'))) {
            throw new BadRequestHttpException('Invalid action');
        }
    }

}