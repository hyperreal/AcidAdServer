<?php

namespace Hyper\AdsBundle\Controller\User;

use Doctrine\ORM\NoResultException;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpFoundation\Response;
use JMS\Payment\CoreBundle\Entity\PaymentInstruction;
use JMS\Payment\CoreBundle\Model\FinancialTransactionInterface;
use Hyper\AdsBundle\Entity\Advertisement;
use Hyper\AdsBundle\DBAL\PayModelType;
use Hyper\AdsBundle\Entity\Banner;
use Hyper\AdsBundle\Entity\BannerZoneReference;
use Hyper\AdsBundle\Entity\Order;
use Hyper\AdsBundle\Entity\Zone;
use Hyper\AdsBundle\Form\BannerType;
use Hyper\AdsBundle\Form\OrderType;
use Hyper\AdsBundle\Form\PaymentType;
use Hyper\AdsBundle\Controller\Controller;
use Hyper\AdsBundle\Exception\NoReferenceException;

class BannerController extends Controller
{
    /**
     * @Route("/new", name="user_banner_new")
     * @Template()
     */
    public function addAction()
    {
        $this->accessDeniedWhenInvalidUser();

        $banner = new Banner();
        $banner->setAdvertiser($this->getUser());
        $bannerType = new BannerType();
        $bannerType->disableDescriptionInput();
        $form = $this->createForm($bannerType, $banner);

        return array(
            'wysiwyg' => 'disabled',
            'form' => $form->createView(),
            'banner' => $banner
        );
    }

    /**
     * @Route("/create", name="user_banner_create")
     * @Method("POST")
     * @Template("HyperAdsBundle:User:Banner/add.html.twig")
     */
    public function createAction(Request $request)
    {
        $this->accessDeniedWhenInvalidUser();

        $banner = new Banner();
        $banner->setAdvertiser($this->getUser());
        $bannerType = new BannerType();
        $bannerType->disableDescriptionInput();
        $form = $this->createForm($bannerType, $banner);
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
            'banner' => $banner,
            'wysiwyg' => 'disabled',
        );
    }

    /**
     * @Route("/", name="user_banner_list")
     * @Template()
     */
    public function listAction()
    {
        $this->accessDeniedWhenInvalidUser();

        $em = $this->getDoctrine()->getManager();
        /** @var $bannerRepository \Hyper\AdsBundle\Entity\AdvertisementRepository */
        $bannerRepository = $em->getRepository('HyperAdsBundle:Banner');
        $bannerList = $bannerRepository->getBannersWithDependenciesByAdvertiser($this->getUser());

        return array(
            'banners' => $bannerList
        );
    }

    /**
     * @Route("/{banner}/zones", name="user_banner_zones")
     * @Template()
     */
    public function zonesAction(Banner $banner)
    {
        $this->accessDeniedWhenInvalidUser($banner);
        /** @var $bannerRepository \Hyper\AdsBundle\Entity\AdvertisementRepository */
        $bannerRepository = $this->getDoctrine()->getManager()->getRepository('HyperAdsBundle:Banner');

        $possibleZones = $bannerRepository->getPossibleZonesForBanner($banner);

        return array(
            'zones' => $possibleZones,
            'banner' => $banner,
        );
    }

    /**
     * @Route("/{banner}/zones/save", name="user_banner_zones_save")
     * @Template("HyperAdsBundle:User:BannerController/zones.html.twig")
     * @Method("POST")
     */
    public function zonesSaveAction(Request $request, $banner)
    {
        $em = $this->getDoctrine()->getManager();
        /** @var $bannerRepository \Hyper\AdsBundle\Entity\AdvertisementRepository */
        $bannerRepository = $em->getRepository('HyperAdsBundle:Banner');
        $banner = $bannerRepository->getBannerWithDependenciesById($banner);

        $this->accessDeniedWhenInvalidUser($banner);

        /** @var $zoneRepository \Hyper\AdsBundle\Entity\ZoneRepository */
        $zoneRepository = $em->getRepository('HyperAdsBundle:Zone');
        $useInZone = $request->get('use_in_zone');
        if (!is_null($useInZone)) {
            $useInZoneIds = array_keys(array_filter($useInZone));

            /** @var $zones \Hyper\AdsBundle\Entity\Zone[] */
            $zones = $zoneRepository->findBy(
                array('id' => $useInZoneIds)
            );

        } else {
            $useInZoneIds = $zones = array();
        }

        $em->beginTransaction();
        $zoneRepository->clearAllButSpecifiedZonesForBanner($banner, $useInZoneIds);

        foreach ($zones as $zone) {
            $reference = $banner->getReferenceInZone($zone->getId());
            if (!empty($reference)) {
                $reference->setActive(true);
                $em->persist($reference);
            } else {
                $this->addReferenceAndOrder($banner, $zone);
            }
        }

        $em->flush();
        $em->commit();
        $this->get('session')->getFlashBag()->set(
            'success',
            $this->trans('all.changes.saved')
        );

        return $this->redirect($this->generateUrl('user_banner_zones', array('banner' => $banner->getId())));
    }

    /**
     * @Route("/{bannerId}/zone/{zoneId}/pay", name="user_banner_pay_in_zone")
     * @Template()
     */
    public function payInZoneAction($bannerId, $zoneId)
    {
        $em = $this->getDoctrine()->getManager();
        /** @var $bannerRepository \Hyper\AdsBundle\Entity\AdvertisementRepository */
        $bannerRepository = $em->getRepository('HyperAdsBundle:Banner');

        $banner = $bannerRepository->getBannerWithDependenciesById($bannerId);
        $this->accessDeniedWhenInvalidUser($banner);

        /** @var $zoneRepository \Hyper\AdsBundle\Entity\ZoneRepository */
        $zoneRepository = $em->getRepository('HyperAdsBundle:Zone');
        /** @var $zone \Hyper\AdsBundle\Entity\Zone */
        $zone = $zoneRepository->find($zoneId);

        $order = new Order();
        $order->setAnnouncement($banner);

        try {
            $reference = $banner->getReferenceInZoneAndThrowWhenNoRef($zone);
        } catch (NoReferenceException $e) {
            $reference = $this->createBannerZoneReference($banner, $zone);
            $em->persist($reference);
            $em->flush($reference);
        }

        $order->setBannerZoneReference($reference);

        $paymentFormType = $this->get('hyper_ads.payment_form_type');
        $paidTo = $banner->getPaidToInZone($zone);
        if (!empty($paidTo)) {
            $paymentFormType->setFromDate($paidTo->modify('+1 day'));
        }
        $form = $this->createForm($paymentFormType, $order);

        return array(
            'banner' => $banner,
            'zone' => $zone,
            'form' => $form->createView(),
            'paidTo' => $paidTo,
        );
    }

    private function createBannerZoneReference($banner, $zone)
    {
        $reference = new BannerZoneReference();
        $reference->setBanner($banner);
        $reference->setZone($zone);
        $reference->setPayModel(PayModelType::PAY_MODEL_DAILY);
        $reference->setProbability(1);
        $reference->setActive(1);
        $reference->setViews(0);
        $reference->setClicks(0);
        return $reference;
    }

    /**
     * @Route("/{banner}/zone/{zone}/calculate", name="user_banner_zone_calculate")
     * @Method("POST")
     */
    public function daysAndCostAction(Request $request, Banner $banner, Zone $zone)
    {
        try {
            $from = new \DateTime($request->get('from'));
            $to = new \DateTime($request->get('to'));
        } catch (\Exception $e) {
            throw new HttpException(400, 'Bad request');
        }

        /** @var $calendar \Hyper\AdsBundle\Helper\BannerZoneCalendar */
        $calendar = $this->get('hyper_ads.banner_zone_calendar');
        $invalidDaysPeriods = $calendar->getCommonDaysForZone($zone, $from, $to);

        $days = $from->diff($to)->days;

        $days = $days - $this->getNumberOfCommonDaysFromPeriodsArray($invalidDaysPeriods);
        /** @var $pricesCalculator \Hyper\AdsBundle\Helper\PricesCalculator */
        $pricesCalculator = $this->get('hyper_ads.prices_calculator');
        $dailyPrice = $pricesCalculator->getDayPriceForZone($zone);

        $currencyAmount = $days * $dailyPrice;

        $commonDaysArray = $this->constructCommonDaysArray($invalidDaysPeriods);
        $response = new Response(
            json_encode(
                array(
                    'days' => $days,
                    'dailyPrice' => $dailyPrice,
                    'price' => sprintf("%.2f", $currencyAmount),
                    'currency' => $this->container->getParameter('ads_default_currency'),
                    'commonDays' => $commonDaysArray,
                )
            )
        );
        $response->headers->set('Content-type', 'application/json');

        return $response;
    }

    /**
     * @param \Hyper\AdsBundle\Helper\DatePeriod[] $periods
     */
    private function getNumberOfCommonDaysFromPeriodsArray(array $periods)
    {
        $numOfDays = 0;

        foreach ($periods as $period) {
            $end = clone $period->getEnd();
            $end->add(new \DateInterval('P1D'));

            $numOfDays += $period->getStart()->diff($end)->days;
        }

        return $numOfDays;
    }

    private function constructCommonDaysArray(array $validDaysPeriods)
    {
        $periods = array();
        foreach ($validDaysPeriods as $period) {
            $periods[] = array(
                's' => $period->getStart()->format('Y-m-d'),
                'e' => $period->getEnd()->format('Y-m-d'),
            );
        }

        return $periods;
    }

    /**
     * @Route("/{bannerId}/zone/{zoneId}/pay/save", name="user_banner_pay_in_zone_save")
     * @Method("POST")
     * @Template("HyperAdsBundle:User:Banner/payInZone.html.twig")
     */
    public function payInZoneSaveAction(Request $request, $bannerId, $zoneId)
    {
        $em = $this->getDoctrine()->getManager();
        /** @var $bannerRepository \Hyper\AdsBundle\Entity\AdvertisementRepository */
        $bannerRepository = $em->getRepository('HyperAdsBundle:Banner');

        $banner = $bannerRepository->getBannerWithDependenciesById($bannerId);
        $this->accessDeniedWhenInvalidUser($banner);

        /** @var $zoneRepository \Hyper\AdsBundle\Entity\ZoneRepository */
        $zoneRepository = $em->getRepository('HyperAdsBundle:Zone');
        /** @var $zone \Hyper\AdsBundle\Entity\Zone */
        $zone = $zoneRepository->find($zoneId);
        $bannerZoneReference = $banner->getReferenceInZone($zone->getId());

        $order = null;
        if (null === $order) {
            $order = new Order();
            $order->setOrderNumber(
                $this->get('hyper_ads.order_number_generator')->getBannerPaymentOrderNumber(
                    $banner,
                    $this->getUser(),
                    $zone
                )
            );
            //$order->setAnnouncement($banner);
            $order->setBannerZoneReference($bannerZoneReference);
        }

        $form = $this->createForm($this->get('hyper_ads.payment_form_type'), $order);
        $form->bind($request);

        if ($form->isValid()) {

            /** @var $ppc \JMS\Payment\CoreBundle\PluginController\PluginController */
            $ppc = $this->get('payment.plugin_controller');

            /** @var $calc \Hyper\AdsBundle\Helper\PricesCalculator */
            $calc = $this->get('hyper_ads.prices_calculator');
            /** @var $payToDate \DateTime */
            $payToDate = $form->get('pay_to')->getData();
            $payFromDate = $form->get('pay_from')->getData();

            /** @var $calendar \Hyper\AdsBundle\Helper\BannerZoneCalendar */
            $calendar = $this->get('hyper_ads.banner_zone_calendar');
            $invalidDaysPeriods = $calendar->getCommonDaysForZone($zone, $payFromDate, $payToDate);

            $days = $payFromDate->diff($payToDate)->days;
            $days -= $this->getNumberOfCommonDaysFromPeriodsArray($invalidDaysPeriods);

            $currencyAmount = $calc->getDayPriceForZone($zone) * $days;

            $systemCurrency = $this->container->getParameter('ads_default_currency');
            $paymentMethods = $this->container->getParameter('banner_payment_methods');

            $ppc->createPaymentInstruction(
                $instruction = new PaymentInstruction(
                    $currencyAmount,
                    $systemCurrency,
                    $paymentMethods[0]
                )
            );

            $order->setAmount($currencyAmount);
            $order->setPaymentFrom($payFromDate);
            $order->setPaymentTo($payToDate);
            $order->setAnnouncement($banner);
            $order->setPaymentInstruction($instruction);

            $em->persist($order);
            $em->persist($banner);
            $em->flush();

            if (FinancialTransactionInterface::STATE_PENDING == $instruction->getState()) {
                /** @var $invoiceAddressRetriever \Hyper\AdsBundle\Payment\InvoiceAddressRetriever */
                $invoiceAddressRetriever = $this->get('hyper_ads.payment.invoice_address_retriever');
                $url = $invoiceAddressRetriever->retrieveUrlForOrder($order);

                $order->setPaymentUrl($url);
                $em->flush();

                return $this->redirect($url);
            }

            //what is going on?
            $this->get('hyper_ads.payments_logger')
                ->warn('what is going on? instruction has not state PENDING but' . $instruction->getState());

            $em->flush();
        }

        return array(
            'banner' => $banner,
            'zone' => $zone,
            'form' => $form->createView()
        );
    }

    private function addReferenceAndOrder(Banner $banner, Zone $zone)
    {
        /** @var $orderNumberGenerator \Hyper\AdsBundle\Helper\OrderNumberGenerator */
        $orderNumberGenerator = $this->get('hyper_ads.order_number_generator');
        $em = $this->getDoctrine()->getManager();

        $ref = new BannerZoneReference();
        $ref->setZone($zone);
        $ref->setBanner($banner);
        $ref->setProbability(1);
        $ref->setPayModel(PayModelType::PAY_MODEL_DAILY);

        $order = new Order();
        $order->setOrderNumber(
            $orderNumberGenerator->getBannerPaymentOrderNumber($banner, $this->getUser(), $zone)
        );
        $order->setAnnouncement($banner);
        $order->setBannerZoneReference($ref);

        $em->persist($ref);
        $em->persist($order);
    }

    /**
     * @Route("/edit/{banner}", name="user_banner_edit")
     * @Template()
     */
    public function editAction(Banner $banner)
    {
        $this->accessDeniedWhenInvalidUser($banner);

        $formType = new BannerType();
        $formType->disableFileInput();
        $formType->disableDescriptionInput();

        $editForm = $this->createForm($formType, $banner);

        return array(
            'editForm' => $editForm->createView(),
            'banner' => $banner,
        );
    }

    /**
     * @Route("/edit/{banner}/save-handler", name="user_banner_save_handler")
     * @Method("POST")
     * @Template("HyperAdsBundle:User:Banner/edit.html.twig")
     */
    public function saveHandlerAction(Request $request, Banner $banner)
    {
        $this->accessDeniedWhenInvalidUser($banner);
        $action = $request->get('action');
        $request->request->remove('action');
        if (!in_array($action, array('update', 'remove'))) {
            throw new BadRequestHttpException('Invalid action');
        }

        $formType = new BannerType();
        $formType->disableFileInput();
        $formType->disableDescriptionInput();
        $editFrom = $this->createForm($formType, $banner);
        $editFrom->bind($request);

        if ($editFrom->isValid()) {
            $this->persistOrRemoveBanner($banner, $action);
            return $this->redirect($this->generateUrl('user_banner_list'));
        }

        return array(
            'editForm' => $editFrom,
            'banner' => $banner,
        );
    }

    private function persistOrRemoveBanner($banner, $action)
    {
        $em = $this->getDoctrine()->getManager();
        if ('update' == $action) {
            $em->persist($banner);
            $this->get('session')->getFlashBag()->add('success', $this->trans('banner.updated'));
        } elseif ('remove' == $action) {
            $em->remove($banner);
            $this->get('session')->getFlashBag()->add('success', $this->trans('banner.removed'));
        }

        $em->flush();
    }

    /**
     * @Route("/edit/{banner}/save", name="user_banner_save")
     * @Method("POST")
     * @Template("HyperAdsBundle:User:Banner/edit.html.twig")
     */
    public function saveAction(Request $request, Banner $banner)
    {
        $this->accessDeniedWhenInvalidUser($banner);

        $formType = new BannerType();
        $formType->disableFileInput();

        $editForm = $this->createForm($formType, $banner);
        $editForm->bind($request);

        if ($editForm->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($banner);
            $em->flush();

            return $this->redirect($this->generateUrl('user_banner_list'));
        }

        return array(
            'editForm' => $editForm,
            'banner' => $banner
        );
    }

    /**
     * @Route("/{banner}/remove", name="user_banner_remove")
     * @Method("POST")
     */
    public function removeAction(Request $request, Banner $banner)
    {
        $this->accessDeniedWhenInvalidUser($banner);

        $form = $this->createDeleteForm($banner->getId());
        $form->bind($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($banner);
            $em->flush();
        }

        return $this->redirect($this->generateUrl('user_banner_list'));
    }

    /**
     * @Route("/{banner}/pay", name="user_banner_pay")
     * @Template()
     */
    public function payAction(Banner $banner)
    {
        $this->accessDeniedWhenInvalidUser($banner);

        /** @var $calculator \Hyper\AdsBundle\Helper\PricesCalculator */
        $calculator = $this->get('hyper_ads.prices_calculator');

        $orderType = new OrderType();
        $orderType->setAnnouncement($banner);

        $order = new Order();
        $order->setAnnouncement($banner);
        $em = $this->getDoctrine()->getManager();
        $zoneRepository = $em->getRepository('HyperAdsBundle:Zone');

        $orderForm = $this->createForm($orderType, $order);

        return array(
            'zones' => $zoneRepository->findAll(),
            'banner' => $banner,
            'orderForm' => $orderForm->createView(),
            'zonesPrices' => $calculator->getPossibleDayPricesForAnnouncement($banner)
        );
    }

    /**
     * @Route("/{banner}/pay/save", name="user_banner_pay_save")
     * @Method("POST")
     * @Template("HyperAdsBundle:User:Banner/pay.html.twig")
     */
    public function paySaveAction(Request $request, Banner $banner)
    {
        $this->accessDeniedWhenInvalidUser($banner);

        $orderType = new OrderType();
        $orderType->setAnnouncement($banner);

        $order = new Order();
        $order->setAnnouncement($banner);

        $orderForm = $this->createForm($orderType, $order);
        $orderForm->bind($request);

        /** @var $calculator \Hyper\AdsBundle\Helper\PricesCalculator */
        $calculator = $this->get('hyper_ads.prices_calculator');
        $em = $this->getDoctrine()->getManager();

        if ($orderForm->isValid()) {
            /** @var $orderNumberGenerator \Hyper\AdsBundle\Helper\OrderNumberGenerator */
            $orderNumberGenerator = $this->get('hyper_ads.order_number_generator');

            /** @var $zone \Hyper\AdsBundle\Entity\Zone */
            $zone = $orderForm->get('zone')->getData();
            $amount = $calculator->getAmountToPayForAnnouncementInZone($banner, $zone);

            $order->setAmount($amount);
            $order->setOrderNumber($orderNumberGenerator->getBannerPaymentOrderNumber($banner, $this->getUser()));
            $order->setZone($zone);

            $paymentMethods = $this->container->getParameter('banner_payment_methods');
            $paymentInstruction = new PaymentInstruction(
                $amount,
                $this->container->getParameter('ads_default_currency'),
                $paymentMethods[0]
            );

            $order->setPaymentInstruction($paymentInstruction);

            $em->persist($paymentInstruction);
            $em->persist($order);
            $em->flush();

            return $this->redirect($this->generateUrl('user_banner_list'));
        }

        $zoneRepository = $em->getRepository('HyperAdsBundle:Zone');

        return array(
            'zones' => $zoneRepository->findAll(),
            'banner' => $banner,
            'orderForm' => $orderForm->createView(),
            'zonePrices' => $calculator->getPossibleDayPricesForAnnouncement($banner)
        );
    }

    /**
     * @Route("/{bannerId}/payments", name="user_banner_payments")
     * @Template()
     */
    public function paymentsAction($bannerId = null)
    {
        /** @var $announcementRepository \Hyper\AdsBundle\Entity\AdvertisementRepository */
        $announcementRepository = $this->getDoctrine()->getManager()->getRepository('HyperAdsBundle:Advertisement');

        try {
            $banner = $announcementRepository->getBannerWithDependenciesById($bannerId);
        } catch (NoResultException $e) {
            throw $this->createNotFoundException('There is no banner of given ID');
        }

        $this->accessDeniedWhenInvalidUser($banner);

        return array(
            'announcement' => $banner,
        );
    }

    private function createDeleteForm($id)
    {
        return $this->createFormBuilder(array('id' => $id))
            ->add('id', 'hidden')
            ->getForm();
    }

    private function accessDeniedWhenInvalidUser(Advertisement $announcement = null)
    {
        /** @var $user \Hyper\AdsBundle\Entity\Advertiser */
        $user = $this->getUser();
        if (null === $user) {
            throw new AccessDeniedException('Only logged in user can perform this action');
        }

        if (null !== $announcement && !$user->hasRole('ROLE_ADMIN') && $announcement->getAdvertiser() != $user) {
            throw new AccessDeniedException("You can edit only your own advertisements when you don't have admin privileges");
        }
    }
}
