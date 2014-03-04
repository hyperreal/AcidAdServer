<?php

namespace Hyper\AdsBundle\Controller;

use Hyper\AdsBundle\Exception\InvalidIpnRequestException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class OmnipayController extends Controller
{
    /**
     * @Route("/bitpay/ipn", name="hyper_ads.omnipay.ipn.bitpay")
     */
    public function bitPayIpnAction()
    {
        try {
            $this->get('hyper_ads.payment_processor.bitpay')->process();
        } catch (InvalidIpnRequestException $e) {
            return new Response($this->getErrorMessage(), 400);
        }

        return new Response('OK');
    }

    private function getErrorMessage()
    {
        return json_encode(
            array(
                'status' => 'FAILED',
                'message' => 'Invalid IPN request'
            )
        );
    }
} 