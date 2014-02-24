<?php

namespace Hyper\AdsBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class OmnipayController extends Controller
{
    /**
     * @Route("/bitpay/ipn", name="hyper_ads.omnipay.bitpay.ipn")
     */
    public function bitPayIpnAction(Request $request)
    {
        return new Response(get_class($this->get('hyper_ads.payment_gateway.bitpay')));
    }
} 