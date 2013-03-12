<?php

namespace Hyper\AdsBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Hyper\AdsBundle\Entity\Order;

class PaymentsController extends Controller
{
    /**
     * @Route("/success/{order}", name="payment_successful")
     * @Template()
     */
    public function successfulPaymentAction(Order $order)
    {
        return $this->paymentRender($order);
    }

    /**
     * @Route("/cancel/{order}", name="payment_canceled")
     * @Template()
     */
    public function canceledPaymentAction(Order $order)
    {
        return $this->paymentRender($order);
    }

    private function paymentRender(Order $order)
    {
        $currentUser = $this->getUser();


        if ($order->getAnnouncement()->getAdvertiser()->getId() != $currentUser->getId()) {
            return $this->redirect($this->generateUrl('_welcome'));
        }

        return array(
            'order' => $order
        );
    }
}
