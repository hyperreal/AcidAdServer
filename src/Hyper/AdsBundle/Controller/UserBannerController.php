<?php

namespace Hyper\AdsBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Hyper\AdsBundle\Entity\Banner;
use Hyper\AdsBundle\Form\BannerType;

class UserBannerController extends Controller
{
    /**
     * @Route("/new", name="user_banner_new")
     * @Template()
     */
    public function addBannerAction()
    {
        /** @var $user \Hyper\AdsBundle\Entity\Advertiser */
        $user = $this->getUser();
        if (null === $user) { //todo remove and migrate to config
            throw new AccessDeniedException('Only trusted user can perform this action');
        }

        $banner = new Banner();
        $banner->setAdvertiser($user);
        $form = $this->createForm(new BannerType(), $banner);

        return array(
            'form' => $form->createView(),
            'banner' => $banner
        );
    }

    /**
     * @Route("/create", name="user_banner_create")
     * @Method("POST")
     * @Template("HyperAdsBundle:UserBanner:addBanner.html.twig")
     */
    public function createBannerAction(Request $request)
    {
        $user = $this->getUser();
        if (null === $user) {
            throw new AccessDeniedException('Only trusted user can perform this action');
        }

        $banner = new Banner();
        $banner->setAdvertiser($user);
        $form = $this->createForm(new BannerType(), $banner);
        $form->bind($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $banner->upload();
            $em->persist($banner);
            $em->flush();

            return $this->redirect($this->generateUrl('user_banner_list'));
        }

        return array(
            'form' => $form->createView(),
            'banner' => $banner
        );
    }

    /**
     * @Route("/", name="user_banner_list")
     * @Template()
     */
    public function bannerListAction()
    {
        $em = $this->getDoctrine()->getManager();

        /** @var $bannerRepository \Hyper\AdsBundle\Entity\BannerRepository */
        $bannerRepository = $em->getRepository('HyperAdsBundle:Banner');

        $bannerList = $bannerRepository->findBy(array(
            'advertiser' => $this->getUser()
        ));

        return array(
            'banners' => $bannerList
        );
    }

    /**
     * @Route("/edit/{banner}", name="user_banner_edit")
     * @Template()
     */
    public function bannerEditAction(Banner $banner)
    {
        $editForm = $this->createForm(new BannerType(), $banner);
        $deleteForm = $this->createDeleteForm($banner->getId());

        return array(
            'editForm' => $editForm->createView(),
            'deleteForm' => $deleteForm->createView(),
            'banner' => $banner,
        );
    }

    /**
     * @Route("/edit/{banner}/save", name="user_banner_save")
     * @Method("POST")
     * @Template("HyperAdsBundle:UserBanner:bannerEdit.html.twig")
     */
    public function bannerSaveAction(Banner $banner)
    {

    }

    private function createDeleteForm($id)
    {
        return $this->createFormBuilder(array('id' => $id))
            ->add('id', 'hidden')
            ->getForm();
    }
}
