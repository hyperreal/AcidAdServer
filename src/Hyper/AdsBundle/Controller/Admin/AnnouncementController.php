<?php

namespace Hyper\AdsBundle\Controller\Admin;

use Hyper\AdsBundle\Entity\Announcement;
use Hyper\AdsBundle\Form\AnnouncementFullType;
use Hyper\AdsBundle\Form\AnnouncementType;
use Hyper\AdsBundle\Controller\Controller;
use Hyper\AdsBundle\Exception\InvalidArgumentException;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

class AnnouncementController extends Controller
{
    /**
     * @var \Doctrine\ORM\EntityManager
     * @DI\Inject("doctrine.orm.entity_manager")
     */
    protected $entityManager;

    /**
     * @Route("/", name="admin_announcement_index")
     * @Template()
     */
    public function indexAction()
    {
        return array(
            'announcements' => $this->entityManager
                ->getRepository('HyperAdsBundle:Announcement')
                ->findAll()
        );
    }

    /**
     * @Route("/{announcement}/edit", name="admin_announcement_edit")
     * @Template()
     */
    public function editAction(Announcement $announcement)
    {
        $form = $this->createForm(new AnnouncementFullType(), $announcement);
        return array(
            'form' => $form->createView(),
            'announcement' => $announcement,
        );
    }

    /**
     * @Route("/{announcement}/update-handler", name="admin_announcement_update_handler")
     * @Template("HyperAdsBundle:Admin:Announcement/edit.html.twig")
     * @Method("POST")
     */
    public function announcementHandlerAction(Request $request, Announcement $announcement)
    {
        $action = $request->get('action');
        $request->request->remove('action');

        if ('delete' == $action) {
            return $this->updateAnnouncement($announcement, $request, 'remove');
        } elseif ('update' == $action) {
            return $this->updateAnnouncement($announcement, $request, 'persist');
        }

        throw new InvalidArgumentException('Invalid action');
    }

    private function updateAnnouncement(Announcement $announcement, Request $request, $action)
    {
        $form = $this->createForm(new AnnouncementFullType(), $announcement);
        $form->bind($request);

        if ($form->isValid()) {
            $this->persistOrRemoveAnnouncement($action, $announcement);
            $this->persistOrRemoveFlash($action);

            return $this->redirect($this->generateUrl('admin_announcement_index'));
        }

        return array(
            'form' => $form->createView(),
            'announcement' => $announcement,
        );
    }

    private function persistOrRemoveFlash($action)
    {
        if ('persist' === $action) {
            $this->get('session')->getFlashBag()->add('success', $this->trans('announcement.saved'));
        } elseif ('remove' === $action) {
            $this->get('session')->getFlashBag()->add('success', $this->trans('announcement.deleted'));
        }
    }

    private function persistOrRemoveAnnouncement($action, $announcement)
    {
        if ('persist' === $action) {
            $this->entityManager->persist($announcement);
        } elseif ('remove' === $action) {
            $this->entityManager->remove($announcement);
        }

        $this->entityManager->flush();
    }
}

