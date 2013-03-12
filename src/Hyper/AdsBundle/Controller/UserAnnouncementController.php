<?php

namespace Hyper\AdsBundle\Controller;

use Hyper\AdsBundle\Entity\Advertisement;
use Hyper\AdsBundle\Entity\Announcement;
use Hyper\AdsBundle\Form\AnnouncementType;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

class UserAnnouncementController extends Controller
{
    /**
     * @var \Doctrine\ORM\EntityManager
     * @DI\Inject("doctrine.orm.entity_manager")
     */
    protected $entityManager;

    /**
     * @Route("/", name="user_announcement_index")
     * @Template()
     */
    public function indexAction()
    {
        return array(
            'announcements' => $this->entityManager
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
     * @Template("HyperAdsBundle:UserAnnouncement:new.html.twig")
     */
    public function createAction(Request $request)
    {
        $announcement = new Announcement();
        $announcement->setAdvertiser($this->getUser());
        $form = $this->createForm(new AnnouncementType(), $announcement);
        $form->bind($request);

        if ($form->isValid()) {
            $this->entityManager->persist($announcement);
            $this->entityManager->flush();

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
        $form = $this->createForm(new AnnouncementType(), $announcement);
        return array(
            'form' => $form->createView(),
            'announcement' => $announcement,
        );
    }

    /**
     * @Route("/{announcement}/save", name="user_announcement_update")
     * @Method("POST")
     */
    public function updateAction(Announcement $announcement, Request $request)
    {

    }

    /**
     * @Route("/{announcement}/delete", name="user_announcement_delete")
     * @Method("POST")
     */
    public function deleteAction(Announcement $announcement, Request $request)
    {

    }

    private function throwUnlessValidUser(Advertisement $advertisement)
    {
        if ($this->getUser()->getId() !== $advertisement->getAdvertiser()->getId()) {
            throw new AccessDeniedException(
                $this->trans('access.denied')
            );
        }
    }
}

