<?php

namespace Hyper\AdsBundle\Controller;

use Hyper\AdsBundle\Exception\PaymentException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Response;

class OmnipayController extends Controller
{
    /**
     * @Route("/bitpay/ipn", name="hyper_ads.omnipay.ipn.bitpay")
     * @Method("POST")
     */
    public function bitPayIpnAction()
    {
        try {
            $this->get('hyper_ads.payment.processor.bitpay')->process();
            $this->logSuccess();
            $response = new Response($this->getSuccessMessage(), 200, array('Content-type' => 'application/json'));
        } catch (PaymentException $e) {
            $response = $this->getBadRequestResponse();
        } catch (\InvalidArgumentException $e) {
            if ($e->getMessage() == '$amount must not be greater than Payment\'s target amount.') {
                $this->logPaymentAlreadyProvided();
                $response = $this->getBadRequestResponse('Order is already paid');
            } else {
                $this->logException($e);
                $response = $this->getServerErrorResponse();
            }
        } catch (\Exception $e) {
            $this->logException($e);
            $response = $this->getServerErrorResponse();
        }

        return $response;
    }

    private function getServerErrorResponse()
    {
        return new Response($this->getErrorMessage(), 500, array('Content-type'));
    }

    private function getBadRequestResponse($message = 'Invalid IPN request')
    {
        return new Response(
            $this->getInvalidRequestMessage($message),
            400,
            array('Content-type' => 'application/json')
        );
    }

    private function getInvalidRequestMessage($message)
    {
        return json_encode(
            array(
                'status' => 'FAILED',
                'message' => $message,
            )
        );
    }

    private function getSuccessMessage()
    {
        return json_encode(
            array(
                'status' => 'OK',
                'message' => 'Payment confirmed',
            )
        );
    }

    private function getErrorMessage()
    {
        return json_encode(
            array(
                'status' => 'FAILED',
                'message' => 'Server error',
            )
        );
    }

    private function logPaymentAlreadyProvided()
    {
        $this->get('hyper_ads.payments_logger')->error(
            'Payment already provided for request',
            array(
                'orderId' => $this->get('hyper_ads.payment.request.bitpay')->getOrderId(),
                'requestId' => $this->get('hyper_ads.payment.request.bitpay')->getId(),
            )
        );
    }

    private function logSuccess()
    {
        $this->get('hyper_ads.payments_logger')->error( //temporary
            'Payment successful',
            array(
                'orderId' => $this->get('hyper_ads.payment.request.bitpay')->getOrderId(),
                'requestId' => $this->get('hyper_ads.payment.request.bitpay')->getId(),
                'amount' => $this->get('hyper_ads.payment.request.bitpay')->getPrice(),
            )
        );
    }

    private function logException(\Exception $e)
    {
        $this->get('hyper_ads.payments_logger')->error(
            'Exception during processing request',
            array(
                'class' => get_class($e),
                'message' => $e->getMessage(),
            )
        );
    }
}