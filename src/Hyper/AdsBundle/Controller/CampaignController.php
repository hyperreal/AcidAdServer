<?php

namespace Hyper\AdsBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Hyper\AdsBundle\Entity\Campaign;
use Hyper\AdsBundle\Form\CampaignType;

/**
 * Campaign controller.
 */
class CampaignController extends Controller
{
    /**
     * Lists all Campaign entities.
     *
     * @Route   ("/{advertiserId}", name="admin_campaign", requirements={"advertiserId" = "\d+"}, defaults={"advertiserId" = 0})
     * @Template()
     */
    public function indexAction($advertiserId = 0)
    {
        /** @var $em \Doctrine\ORM\EntityManager */
        $em = $this->getDoctrine()->getManager();

        if ($advertiserId === '0') {
            $entities = $em->getRepository('HyperAdsBundle:Campaign')->findAll();

            return array(
                'entities' => $entities,
            );
        } else {
            $advertiser = $em->getRepository('HyperAdsBundle:Advertiser')->find($advertiserId);

            if (empty($advertiser)) {
                throw $this->createNotFoundException('Advertiser not found.');
            }

            $entities = $em->getRepository('HyperAdsBundle:Campaign')->findBy(
                array(
                    'advertiser' => $advertiser,
                )
            );

            return array(
                'entities'      => $entities,
                'advertiser'    => $advertiser
            );
        }
    }

    /**
     * Finds and displays a Campaign entity.
     *
     * @Route   ("/{id}/show", name="admin_campaign_show")
     * @Template()
     */
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('HyperAdsBundle:Campaign')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Campaign entity.');
        }

        $deleteForm = $this->createDeleteForm($id);

        return array(
            'entity'      => $entity,
            'delete_form' => $deleteForm->createView(),
        );
    }

    /**
     * Displays a form to create a new Campaign entity.
     *
     * @Route   ("/new", name="admin_campaign_new")
     * @Template()
     */
    public function newAction()
    {
        $entity = new Campaign();
        $form   = $this->createForm(new CampaignType(), $entity);

        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
        );
    }

    /**
     * Creates a new Campaign entity.
     *
     * @Route   ("/create", name="admin_campaign_create")
     * @Method  ("POST")
     * @Template("HyperAdsBundle:Campaign:new.html.twig")
     */
    public function createAction(Request $request)
    {
        $entity = new Campaign();
        $form   = $this->createForm(new CampaignType(), $entity);
        $form->bind($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('admin_campaign_show', array('id' => $entity->getId())));
        }

        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
        );
    }

    /**
     * Displays a form to edit an existing Campaign entity.
     *
     * @Route   ("/{id}/edit", name="admin_campaign_edit")
     * @Template()
     */
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('HyperAdsBundle:Campaign')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Campaign entity.');
        }

        $editForm   = $this->createForm(new CampaignType(), $entity);
        $deleteForm = $this->createDeleteForm($id);

        return array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        );
    }

    /**
     * Edits an existing Campaign entity.
     *
     * @Route   ("/{id}/update", name="admin_campaign_update")
     * @Method  ("POST")
     * @Template("HyperAdsBundle:Campaign:edit.html.twig")
     */
    public function updateAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('HyperAdsBundle:Campaign')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Campaign entity.');
        }

        $deleteForm = $this->createDeleteForm($id);
        $editForm   = $this->createForm(new CampaignType(), $entity);
        $editForm->bind($request);

        if ($editForm->isValid()) {
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('admin_campaign_edit', array('id' => $id)));
        }

        return array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        );
    }

    /**
     * Deletes a Campaign entity.
     *
     * @Route ("/{id}/delete", name="admin_campaign_delete")
     * @Method("POST")
     */
    public function deleteAction(Request $request, $id)
    {
        $form = $this->createDeleteForm($id);
        $form->bind($request);

        if ($form->isValid()) {
            $em     = $this->getDoctrine()->getManager();
            $entity = $em->getRepository('HyperAdsBundle:Campaign')->find($id);

            if (!$entity) {
                throw $this->createNotFoundException('Unable to find Campaign entity.');
            }

            $em->remove($entity);
            $em->flush();
        }

        return $this->redirect($this->generateUrl('admin_campaign'));
    }

    private function createDeleteForm($id)
    {
        return $this->createFormBuilder(array('id' => $id))
            ->add('id', 'hidden')
            ->getForm();
    }
}
