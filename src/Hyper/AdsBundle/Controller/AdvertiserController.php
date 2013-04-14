<?php

namespace Hyper\AdsBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
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
     * @Template()`
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
     * @Route   ("/create", name="admin_advertiser_create")
     * @Method  ("POST")
     * @Template("HyperAdsBundle:Advertiser:new.html.twig")
     */
    public function createAction(Request $request)
    {
        $entity = new Advertiser();
        $form   = $this->createForm(new AdvertiserType(), $entity);
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
     * @Route   ("/{id}/edit", name="admin_advertiser_edit")
     * @Template()
     */
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('HyperAdsBundle:Advertiser')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Advertiser entity.');
        }

        $editForm   = $this->createForm(new AdvertiserType(), $entity);
        $deleteForm = $this->createDeleteForm($id);

        return array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        );
    }

    /**
     * @Route("/{id}/update", name="admin_advertiser_update_handler")
     * @Method("POST")
     * @Template("HyperAdsBundle:Advertiser:edit.html.twig")
     */
    public function updateHandlerAction(Request $request, Advertiser $id)
    {
        $action = $request->get('action');
        $request->request->remove('action');

        if (!in_array($action, array('update', 'delete'))) {
            throw new BadRequestHttpException('Invalid action');
        }

        return $this->updateAdvertiser($id, $request, $action);
    }

    private function updateAdvertiser(Advertiser $advertiser, Request $request, $action)
    {
        $form = $this->createForm(new AdvertiserType(), $advertiser);
        $form->bind($request);

        if ($form->isValid()) {
            $this->persistOrRemoveAdvertiser($advertiser, $action);
            $this->persistOrRemoveFlash($action);
            return $this->redirect($this->generateUrl('admin_advertiser_edit', array('id' => $advertiser->getId())));
        }

        return array(
            'entity'      => $advertiser,
            'edit_form' => $form
        );
    }

    private function persistOrRemoveFlash($action)
    {
        if ('update' == $action) {
            $this->get('session')->getFlashBag()->add('success', $this->trans('advertiser.updated'));
        } elseif ('delete' == $action) {
            $this->get('session')->getFlashBag()->add('success', $this->trans('advertiser.removed'));
        }
    }

    private function persistOrRemoveAdvertiser(Advertiser $advertiser, $action)
    {
        if ('update' == $action) {
            $this->get('doctrine.orm.entity_manager')->persist($advertiser);
        } elseif ('delete' == $action) {
            $this->get('doctrine.orm.entity_manager')->remove($advertiser);
        }

        $this->get('doctrine.orm.entity_manager')->flush();
    }

    private function createDeleteForm($id)
    {
        return $this->createFormBuilder(array('id' => $id))
            ->add('id', 'hidden')
            ->getForm();
    }
}
