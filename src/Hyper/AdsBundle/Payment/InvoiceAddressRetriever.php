<?php

namespace Hyper\AdsBundle\Payment;

use Hyper\AdsBundle\Exception\PaymentException;
use Symfony\Component\DependencyInjection\ContainerInterface;

class InvoiceAddressRetriever
{
    /** @var \Symfony\Component\DependencyInjection\ContainerInterface */
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function retrieveUrlForOrder(OrderInterface $order)
    {
        $paymentMethod = $this->getPaymentMethodFromSystemName($order->getPaymentInstruction()->getPaymentSystemName());
        /** @var $gateway \Omnipay\Common\AbstractGateway */
        $gateway = $this->container->get('hyper_ads.payment.gateway.' . $paymentMethod);

        /** @var $response \Omnipay\BitPay\Message\PurchaseResponse */
        $response = $gateway->purchase($this->getParameters($order))->send();
        if ($response->isRedirect()) {
            return $response->getRedirectUrl();
        } elseif (!$response->isSuccessful()) {
            throw new PaymentException('Problem with getting payment URL. Cause: ' . $response->getMessage());
        } else {
            throw new PaymentException('Payment that does not return redirects to the payment page are not supported');
        }
    }

    private function getParameters(OrderInterface $order)
    {
        /** @var $paramsProvider \Hyper\AdsBundle\Payment\ParamsProviders */
        $paramsProvider = $this->container->get('hyper_ads.payment.params_providers');
        $params = $paramsProvider->getParametersFromOrder($order);

        $this->container->get('hyper_ads.payments_logger')->debug(var_export($params, true));

        return $params;
    }

    private function getPaymentMethodFromSystemName($paymentSystemName)
    {
        if (strpos($paymentSystemName, 'omnipay_') === false) {
            throw new PaymentException('Payment method cannot be obtained from system name: ' . $paymentSystemName);
        }

        return substr($paymentSystemName, 8);
    }
} 