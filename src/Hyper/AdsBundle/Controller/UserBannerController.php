<?php

namespace Hyper\AdsBundle\Controller;

use Doctrine\ORM\NoResultException;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpFoundation\Response;
use JMS\Payment\CoreBundle\Entity\PaymentInstruction;
use JMS\Payment\CoreBundle\Model\FinancialTransactionInterface;
use Wikp\PaymentMtgoxBundle\Plugin\MtgoxPaymentPlugin;
use Wikp\PaymentMtgoxBundle\Mtgox\RequestType\MtgoxTransactionUrlRequest;
use Hyper\AdsBundle\Entity\Announcement;
use Hyper\AdsBundle\DBAL\PayModelType;
use Hyper\AdsBundle\Entity\Banner;
use Hyper\AdsBundle\Entity\BannerZoneReference;
use Hyper\AdsBundle\Entity\Order;
use Hyper\AdsBundle\Entity\Zone;
use Hyper\AdsBundle\Form\BannerType;
use Hyper\AdsBundle\Form\OrderType;
use Hyper\AdsBundle\Form\PaymentType;
use Hyper\AdsBundle\Helper\PaymentDaysCalculator;
use Hyper\AdsBundle\Exception\NoReferenceException;

class UserBannerController extends Controller
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
     * @Template("HyperAdsBundle:UserBanner:add.html.twig")
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
        /** @var $bannerRepository \Hyper\AdsBundle\Entity\AnnouncementRepository */
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
        /** @var $bannerRepository \Hyper\AdsBundle\Entity\AnnouncementRepository */
        $bannerRepository = $this->getDoctrine()->getManager()->getRepository('HyperAdsBundle:Banner');

        $possibleZones = $bannerRepository->getPossibleZonesForBanner($banner);

        return array(
            'zones' => $possibleZones,
            'banner' => $banner,
        );
    }

    /**
     * @Route("/{banner}/zones/save", name="user_banner_zones_save")
     * @Template("HyperAdsBundle:UserBannerController:zones.html.twig")
     * @Method("POST")
     */
    public function zonesSaveAction(Request $request, $banner)
    {
        $em = $this->getDoctrine()->getManager();
        /** @var $bannerRepository \Hyper\AdsBundle\Entity\AnnouncementRepository */
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
        $this->get('session')->setFlash(
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
        /** @var $bannerRepository \Hyper\AdsBundle\Entity\AnnouncementRepository */
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

        $paymentFormType = new PaymentType();
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

        $bannerZoneReference = $banner->getReferenceInZone($zone->getId());
        $daysCalculator = new PaymentDaysCalculator($bannerZoneReference);
        $days = $daysCalculator->getNumberOfDaysToPay($from, $to);
        /** @var $pricesCalculator \Hyper\AdsBundle\Helper\PricesCalculator */
        $pricesCalculator = $this->get('hyper_ads.prices_calculator');
        $dailyPrice = $pricesCalculator->getDayPriceForZone($zone);

        /** @var $calendar \Hyper\AdsBundle\Helper\BannerZoneCalendar */
        $calendar = $this->get('hyper_ads.banner_zone_calendar');
        $validDaysPeriods = $calendar->getCommonDaysForZone($zone, $from, $to);

        $response = new Response(
            json_encode(
                array(
                    'days' => $days,
                    'dailyPrice' => $dailyPrice,
                    'price' => $days * $dailyPrice,
                    'currency' => $this->container->getParameter('ads_default_currency'),
                    'common_days' => $this->constructCommonDaysArray($validDaysPeriods),
                )
            )
        );
        $response->headers->set('Content-type', 'application/json');

        return $response;
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
     * @Template()
     */
    public function payInZoneSaveAction(Request $request, $bannerId, $zoneId)
    {
        $em = $this->getDoctrine()->getManager();
        /** @var $bannerRepository \Hyper\AdsBundle\Entity\AnnouncementRepository */
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

        $form = $this->createForm(new PaymentType(), $order);
        $form->bind($request);

        if ($form->isValid()) {

            /** @var $ppc \JMS\Payment\CoreBundle\PluginController\PluginController */
            $ppc = $this->get('payment.plugin_controller');
            $ppc->addPlugin($this->get('wikp_payment_mtgox.plugin'));

            /** @var $calc \Hyper\AdsBundle\Helper\PricesCalculator */
            $calc = $this->get('hyper_ads.prices_calculator');
            /** @var $payToDate \DateTime */
            $payToDate = $form->get('pay_to')->getData();
            $payFromDate = $form->get('pay_from')->getData();

            $daysCalculator = new PaymentDaysCalculator($bannerZoneReference);

            $days = $daysCalculator->getNumberOfDaysToPay($payFromDate, $payToDate);
            $currencyAmount = $calc->getDayPriceForZone($zone) * $days;

            /** @var $exchange \Wikp\PaymentMtgoxBundle\Mtgox\BitcoinExchangeInterface */
            $exchange = $this->get('wikp_payment_mtgox.exchange');
            $amount = $exchange->convertToBitcoins($currencyAmount);

            $ppc->createPaymentInstruction(
                $instruction = new PaymentInstruction(
                    $amount,
                    'BTC',
                    MtgoxPaymentPlugin::SYSTEM_NAME
                )
            );

            $order->setAmount($amount);
            $order->setPaymentFrom($payFromDate);
            $order->setPaymentTo($payToDate);
            $order->setAnnouncement($banner);
            $order->setPaymentInstruction($instruction);

            $em->persist($order);
            $em->persist($banner);
            $em->flush();

            if (FinancialTransactionInterface::STATE_PENDING == $instruction->getState()) {
                $urlRequest = new MtgoxTransactionUrlRequest();
                $urlRequest->setAmount($amount);
                $urlRequest->setIpnUrl($this->generateUrl('wikp_payment_mtgox_ipn', array(), true));
                $urlRequest->setDescription(
                    $this->trans('payment.info', array('%orderNumber%' => $order->getOrderNumber()))
                );
                $urlRequest->setAdditionalData($order->getId());
                $urlRequest->setCurrency('BTC');
                $urlRequest->setReturnSuccess(
                    $this->generateUrl('payment_successful', array('order' => $order->getId()), true)
                );
                $urlRequest->setReturnFailure(
                    $this->generateUrl('payment_canceled', array('order' => $order->getId()), true)
                );

                $url = $this->get('wikp_payment_mtgox.plugin')->getMtgoxTransactionUrl($urlRequest);

                $order->setPaymentUrl($url);
                $em->flush();

                return $this->redirect($url);
            }

            $em->flush();

            return array(
                'form' => $form->createView(),
                'banner' => $banner,
                'zone' => $zone
            );
        }

        return $this->renderView(
            'HyperAdsBundle:UserBanner:payInZone.html.twig',
            array(
                'banner' => $banner,
                'zone' => $zone,
                'form' => $form->createView()
            )
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
     * @Template("HyperAdsBundle:UserBanner:edit.html.twig")
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

        $deleteForm = $this->createDeleteForm($banner->getId());

        return array(
            'editForm' => $editForm,
            'deleteForm' => $deleteForm,
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
     * @Template("HyperAdsBundle:UserBanner.pay.html.twig")
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

            $paymentInstruction = new PaymentInstruction(
                $amount,
                MtgoxPaymentPlugin::CURRENCY_NAME,
                MtgoxPaymentPlugin::SYSTEM_NAME
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
        /** @var $announcementRepository \Hyper\AdsBundle\Entity\AnnouncementRepository */
        $announcementRepository = $this->getDoctrine()->getManager()->getRepository('HyperAdsBundle:Announcement');

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

    private function accessDeniedWhenInvalidUser(Announcement $announcement = null)
    {
        /** @var $user \Hyper\AdsBundle\Entity\Advertiser */
        $user = $this->getUser();
        if (null === $user) {
            throw new AccessDeniedException('Only logged in user can perform this action');
        }

        if (null !== $announcement && !$user->hasRole('ROLE_ADMIN') && $announcement->getAdvertiser() != $user) {
            throw new AccessDeniedException("You can edit only your own announcements when you don't have admin privileges");
        }
    }
}
