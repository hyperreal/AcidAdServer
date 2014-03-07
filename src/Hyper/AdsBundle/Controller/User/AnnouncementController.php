<?php

namespace Hyper\AdsBundle\Controller\User;

use Hyper\AdsBundle\Entity\Advertisement;
use Hyper\AdsBundle\Entity\Announcement;
use Hyper\AdsBundle\Form\AnnouncementType;
use Hyper\AdsBundle\Controller\Controller;
use Hyper\AdsBundle\Exception\InvalidArgumentException;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Wikp\PaymentMtgoxBundle\Mtgox\RequestType\MtgoxTransactionUrlRequest;

class AnnouncementController extends Controller
{
    /**
     * @Route("/", name="user_announcement_index")
     * @Template()
     */
    public function indexAction()
    {
        return array(
            'announcements' => $this->get('doctrine.orm.entity_manager')
                ->getRepository('HyperAdsBundle:Announcement')
                ->getAnnouncementsForUser(
                    $this->getUser()
                )
        );
    }

    /**
     * @Route("/add", name="user_announcement_new")
     * @Template()
     */
    public function newAction()
    {
        return array(
            'form' => $this->createForm(new AnnouncementType(), new Announcement())->createView()
        );
    }

    /**
     * @Route("/save", name="user_announcement_save")
     * @Method("POST")
     * @Template("HyperAdsBundle:User:Announcement/new.html.twig")
     */
    public function createAction(Request $request)
    {
        $announcement = new Announcement();
        $announcement->setAdvertiser($this->getUser());
        $form = $this->createForm(new AnnouncementType(), $announcement);
        $form->bind($request);

        if ($form->isValid()) {
            $this->get('doctrine.orm.entity_manager')->persist($announcement);
            $this->get('doctrine.orm.entity_manager')->flush();

            return $this->redirect($this->generateUrl('user_announcement_index'));
        }

        return array(
            'form' => $form->createView(),
        );
    }

    /**
     * @Route("/{announcement}/show", name="user_announcement_show")
     * @Template()
     */
    public function showAction(Announcement $announcement)
    {
        $this->throwUnlessValidUser($announcement);
        return array(
            'announcement' => $announcement
        );
    }

    /**
     * @Route("/{announcement}/edit", name="user_announcement_edit")
     * @Template()
     */
    public function editAction(Announcement $announcement)
    {
        $this->throwUnlessValidUser($announcement);
        $form = $this->createForm(new AnnouncementType(), $announcement);
        return array(
            'form' => $form->createView(),
            'announcement' => $announcement,
        );
    }

    /**
     * @Route("/{announcement}/update-handler", name="user_announcement_update_handler")
     * @Template("HyperAdsBundle:User:Announcement/edit.html.twig")
     * @Method("POST")
     */
    public function announcementHandlerAction(Request $request, Announcement $announcement)
    {
        $this->throwUnlessValidUser($announcement);
        $action = $request->get('action');
        $request->request->remove('action');

        if ($this->trans('delete') === $action) {
            return $this->updateAnnouncement($announcement, $request, 'remove');
        } elseif ($this->trans('save') === $action) {
            return $this->updateAnnouncement($announcement, $request, 'persist');
        }

        throw new InvalidArgumentException('Invalid action');
    }

    private function updateAnnouncement(Announcement $announcement, Request $request, $action)
    {
        $form = $this->createForm(new AnnouncementType(), $announcement);
        $form->bind($request);

        if ($form->isValid()) {

            $this->paymentScreen($announcement);

            $this->persistOrRemoveAnnouncement($action, $announcement);
            $this->persistOrRemoveFlash($action);

            return $this->redirect($this->generateUrl('user_announcement_index'));
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
            $this->get('doctrine.orm.entity_manager')->persist($announcement);
        } elseif ('remove' === $action) {
            $this->get('doctrine.orm.entity_manager')->remove($announcement);
        }

        $this->get('doctrine.orm.entity_manager')->flush();
    }

    private function throwUnlessValidUser(Advertisement $advertisement)
    {
        if ($this->getUser()->getId() !== $advertisement->getAdvertiser()->getId()) {
            throw new AccessDeniedException(
                $this->trans('access.denied')
            );
        }
    }

    private function paymentScreen(Announcement $announcement)
    {
        $request = new MtgoxTransactionUrlRequest();
        $request->setCurrency($this->container->getParameter('announcement_edit_currency'));
        $request->setIpnUrl($this->generateUrl('wikp_payment_mtgox_ipn'));
        $request->setAmount($this->container->getParameter('announcement_edit_cost'));
        $request->setAdditionalData($announcement->getId());



    }
}