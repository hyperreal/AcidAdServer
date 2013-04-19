<?php

namespace Hyper\AdsBundle\Controller\Admin;

use \Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Hyper\AdsBundle\Entity\Page;
use Hyper\AdsBundle\Form\PageType;
use Hyper\AdsBundle\Controller\Controller;

class PageController extends Controller
{
    /**
     * Lists all Page entities.
     *
     * @Route   ("/", name="admin_page")
     * @Template()
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $entities = $em->getRepository('HyperAdsBundle:Page')->findAll();

        return array(
            'entities' => $entities,
        );
    }

    /**
     * Finds and displays a Page entity.
     *
     * @Route   ("/{id}/show", name="admin_page_show")
     * @Template()
     */
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('HyperAdsBundle:Page')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Page entity.');
        }

        $deleteForm = $this->createDeleteForm($id);

        return array(
            'entity'      => $entity,
            'delete_form' => $deleteForm->createView(),
        );
    }

    /**
     * Displays a form to create a new Page entity.
     *
     * @Route   ("/new", name="admin_page_new")
     * @Template()
     */
    public function newAction()
    {
        $entity = new Page();
        $form   = $this->createForm(new PageType(), $entity);

        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
        );
    }

    /**
     * Creates a new Page entity.
     *
     * @Route   ("/create", name="admin_page_create")
     * @Method  ("POST")
     * @Template("HyperAdsBundle:Admin:Page/new.html.twig")
     */
    public function createAction(Request $request)
    {
        $entity = new Page();
        $form   = $this->createForm(new PageType(), $entity);
        $form->bind($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('admin_page_show', array('id' => $entity->getId())));
        }

        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
        );
    }

    /**
     * Displays a form to edit an existing Page entity.
     *
     * @Route   ("/{id}/edit", name="admin_page_edit")
     * @Template()
     */
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('HyperAdsBundle:Page')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Page entity.');
        }

        $editForm   = $this->createForm(new PageType(), $entity);
        $deleteForm = $this->createDeleteForm($id);

        return array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        );
    }

    /**
     * @Route("/{id}/update-handler", name="admin_page_update_handler")
     * @Method("POST")
     * @Template("HyperAdsBundle:Admin:Page/edit.html.twig")
     */
    public function updateHandlerAction(Request $request, Page $page)
    {
        $action = $request->get('action');
        $request->request->remove('action');

        $form = $this->createForm(new PageType(), $page);
        $form->bind($request);

        if ($form->isValid()) {
            $this->persistOrRemovePage($page, $action);
            $this->persistOrRemoveFlash($action);

            return $this->redirect($this->generateUrl('admin_page'));
        }

        return array(
            'entity' => $page,
            'edit_form' => $form->createView(),
        );
    }

    private function persistOrRemoveFlash($action)
    {
        if ('update' == $action) {
            $this->get('session')->getFlashBag()->add('success', $this->trans('page.updated'));
        } elseif ('remove' == $action) {
            $this->get('session')->getFlashBag()->add('success', $this->trans('page.removed'));
        }
    }

    private function persistOrRemovePage(Page $page, $action)
    {
        if ('update' == $action) {
            $this->get('doctrine.orm.entity_manager')->persist($page);
        } elseif ('remove' == $action) {
            $this->get('doctrine.orm.entity_manager')->remove($page);
        } else {
            throw new BadRequestHttpException('Invalid action');
        }

        $this->get('doctrine.orm.entity_manager')->flush();
    }

    /**
     * Edits an existing Page entity.
     *
     * @Route   ("/{id}/update", name="admin_page_update")
     * @Method  ("POST")
     * @Template("HyperAdsBundle:Admin:Page/edit.html.twig")
     */
    public function updateAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('HyperAdsBundle:Page')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Page entity.');
        }

        $deleteForm = $this->createDeleteForm($id);
        $editForm   = $this->createForm(new PageType(), $entity);
        $editForm->bind($request);

        if ($editForm->isValid()) {
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('admin_page_edit', array('id' => $id)));
        }

        return array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
        );
    }

    /**
     * Deletes a Page entity.
     *
     * @Route ("/{id}/delete", name="admin_page_delete")
     * @Method("POST")
     */
    public function deleteAction(Request $request, $id)
    {
        $form = $this->createDeleteForm($id);
        $form->bind($request);

        if ($form->isValid()) {
            $em     = $this->getDoctrine()->getManager();
            $entity = $em->getRepository('HyperAdsBundle:Page')->find($id);

            if (!$entity) {
                throw $this->createNotFoundException('Unable to find Page entity.');
            }

            $em->remove($entity);
            $em->flush();
        }

        return $this->redirect($this->generateUrl('admin_page'));
    }

    private function createDeleteForm($id)
    {
        return $this->createFormBuilder(array('id' => $id))
            ->add('id', 'hidden')
            ->getForm();
    }
}
