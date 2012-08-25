<?php

namespace Hyper\AdsBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Hyper\AdsBundle\Entity\Advertiser;
use Hyper\AdsBundle\Form\AdvertiserType;

/**
 * Advertiser controller.
 */
class AdvertiserController extends Controller
{
    /**
     * Lists all Advertiser entities.
     *
     * @Route("/", name="admin_advertiser")
     * @Template()
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $entities = $em->getRepository('HyperAdsBundle:Advertiser')->findAll();

        return array(
            'entities' => $entities,
        );
    }

    /**
     * Finds and displays a Advertiser entity.
     *
     * @Route("/{id}/show", name="admin_advertiser_show")
     * @Template()
     */
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('HyperAdsBundle:Advertiser')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Advertiser entity.');
        }

        $deleteForm = $this->createDeleteForm($id);

        return array(
            'entity'      => $entity,
            'delete_form' => $deleteForm->createView(),
        );
    }

    /**
     * Displays a form to create a new Advertiser entity.
     *
     * @Route("/new", name="admin_advertiser_new")
     * @Template()
     */
    public function newAction()
    {
        $entity = new Advertiser();
        $form   = $this->createForm(new AdvertiserType(), $entity);

        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
        );
    }

    /**
     * Creates a new Advertiser entity.
     *
     * @Route("/create", name="admin_advertiser_create")
     * @Method("POST")
     * @Template("HyperAdsBundle:Advertiser:new.html.twig")
     */
    public function createAction(Request $request)
    {
        $entity  = new Advertiser();
        $form = $this->createForm(new AdvertiserType(), $entity);
        $form->bind($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('admin_advertiser_show', array('id' => $entity->getId())));
        }

        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
        );
    }

    /**
     * Displays a form to edit an existing Advertiser entity.
     *
     * @Route("/{id}/edit", name="admin_advertiser_edit")
     * @Template()
     */
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('HyperAdsBundle:Advertiser')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Advertiser entity.');
        }

        $editForm = $this->createForm(new AdvertiserType(), $entity);
        $deleteForm = $this->createDeleteForm($id);

        return array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        );
    }

    /**
     * Edits an existing Advertiser entity.
     *
     * @Route("/{id}/update", name="admin_advertiser_update")
     * @Method("POST")
     * @Template("HyperAdsBundle:Advertiser:edit.html.twig")
     */
    public function updateAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('HyperAdsBundle:Advertiser')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Advertiser entity.');
        }

        $deleteForm = $this->createDeleteForm($id);
        $editForm = $this->createForm(new AdvertiserType(), $entity);
        $editForm->bind($request);

        if ($editForm->isValid()) {
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('admin_advertiser_edit', array('id' => $id)));
        }

        return array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        );
    }

    /**
     * Deletes a Advertiser entity.
     *
     * @Route("/{id}/delete", name="admin_advertiser_delete")
     * @Method("POST")
     */
    public function deleteAction(Request $request, $id)
    {
        $form = $this->createDeleteForm($id);
        $form->bind($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $entity = $em->getRepository('HyperAdsBundle:Advertiser')->find($id);

            if (!$entity) {
                throw $this->createNotFoundException('Unable to find Advertiser entity.');
            }

            $em->remove($entity);
            $em->flush();
        }

        return $this->redirect($this->generateUrl('admin_advertiser'));
    }

    private function createDeleteForm($id)
    {
        return $this->createFormBuilder(array('id' => $id))
            ->add('id', 'hidden')
            ->getForm()
        ;
    }
}
