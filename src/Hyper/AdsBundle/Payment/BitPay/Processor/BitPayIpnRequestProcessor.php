<?php

namespace Hyper\AdsBundle\Payment\BitPay\Processor;

use Doctrine\ORM\EntityManager;
use Hyper\AdsBundle\Exception\InvalidIpnRequestException;
use Hyper\AdsBundle\Exception\PaymentException;
use Hyper\AdsBundle\Payment\BitPay\BitPayOrderApprovalDeterminer;
use Hyper\AdsBundle\Payment\BitPay\OmnipayBitPayPaymentPlugin;
use Hyper\AdsBundle\Payment\BitPay\Requests\BitPayIpnRequest;
use Hyper\AdsBundle\Payment\OrderInterface;
use Hyper\AdsBundle\Payment\Util\OrderHashGeneratorInterface;
use JMS\Payment\CoreBundle\PluginController\PluginController;
use JMS\Payment\CoreBundle\PluginController\Result;
use Psr\Log\LoggerInterface;

class BitPayIpnRequestProcessor
{
    /** @var \Hyper\AdsBundle\Payment\BitPay\Requests\BitPayIpnRequest */
    private $request;

    /** @var \Psr\Log\LoggerInterface */
    private $logger;

    /** @var \Doctrine\ORM\EntityManager */
    private $entityManager;

    /** @var \Hyper\AdsBundle\Payment\BitPay\BitPayOrderApprovalDeterminer */
    private $approvalDeterminer;

    /** @var \JMS\Payment\CoreBundle\PluginController\PluginController */
    private $pluginController;

    /** @var \Hyper\AdsBundle\Payment\Util\OrderHashGeneratorInterface */
    private $hashOrderGenerator;

    public function __construct(
        BitPayIpnRequest $request,
        EntityManager $entityManager,
        OmnipayBitPayPaymentPlugin $paymentPlugin,
        PluginController $pluginController,
        BitPayOrderApprovalDeterminer $approvalDeterminer,
        LoggerInterface $logger,
        OrderHashGeneratorInterface $hashOrderGenerator
    ) {
        $this->request = $request;
        $this->logger = $logger;
        $this->entityManager = $entityManager;
        $this->approvalDeterminer = $approvalDeterminer;
        $this->pluginController = $pluginController;
        $this->pluginController->addPlugin($paymentPlugin);
        $this->hashOrderGenerator = $hashOrderGenerator;
    }

    public function process()
    {
        $this->preValidate();
		$this->logger->info('IPN request: ', array(var_export($this->request, true)));
        $order = $this->getOrder();
        $this->checkHash($order);

        if ($this->request->isNew()) {
            $this->logger->info(
                'Bitpay ipn request is not yet confirmed',
                array(
                    'id' => $this->request->getId(),
                    'status' => $this->request->getStatus(),
                )
            );
            return;
        }

        if ($this->approvalDeterminer->shouldApprove($this->request)) {
            $this->savePaymentData($order);
            $this->acceptOrder($order);
        } elseif ($this->approvalDeterminer->shouldCancel($this->request)) {
            $this->cancelOrder($order);
        }
    }

    private function checkHash(OrderInterface $order)
    {
        if ($this->request->getHash() != ($hashOrder = $this->hashOrderGenerator->hashOrder($order))) {
            $this->logger->info(
                'Bitpay ipn request is invalid due to invalid hash',
                array(
                    'id' => $this->request->getId(),
                    'orderId' => $this->request->getOrderId(),
                    'requestHash' => $this->request->getHash(),
                    'orderHash' => $hashOrder,
                )
            );
            throw new InvalidIpnRequestException();
        }
    }

    private function savePaymentData(OrderInterface $order)
    {
        $payment = $this->getPaymentFromOrder($order);
        $result = $this->pluginController->approveAndDeposit($payment->getId(), $this->request->getPrice());
        if (Result::STATUS_PENDING === $result->getStatus()) {
            $this->logger->info(
                'Payment for order is pending',
                array('orderId' => $order->getId(), 'requestId' => $this->request->getId())
            );
            throw $result->getPluginException();
        } elseif (Result::STATUS_SUCCESS !== $result->getStatus()) {
            throw new PaymentException();
        }
    }

    private function acceptOrder(OrderInterface $order)
    {
        $this->logger->info(
            'Payment for order was successful',
            array('orderId' => $order->getId(), 'requestId' => $this->request->getId())
        );
        $order->approve();
        $this->persistOrder($order);
    }

    private function cancelOrder(OrderInterface $order)
    {
        $this->logger->info(
            'Payment for order was unsuccessful',
            array('orderId' => $order->getId(), 'requestId' => $this->request->getId())
        );
        $order->cancel();
        $this->persistOrder($order);
    }

    /**
     * @return \Hyper\AdsBundle\Entity\Order
     */
    private function getOrder()
    {
        return $this->entityManager->getRepository('HyperAdsBundle:Order')->find($this->request->getOrderId());
    }

    private function persistOrder(OrderInterface $order)
    {
        $this->entityManager->persist($order);
        $this->entityManager->flush($order);
    }

    /**
     * @return \JMS\Payment\CoreBundle\Model\PaymentInterface
     */
    private function getPaymentFromOrder(OrderInterface $order)
    {
        if (null === ($pendingTransaction = $order->getPaymentInstruction()->getPendingTransaction())) {
            $this->logger->info(
                'Pending transaction for order not found',
                array(
                    'orderId' => $order->getId(),
                    'requestId' => $this->request->getId(),
                )
            );
            $payment = $this->pluginController->createPayment(
                $order->getPaymentInstruction()->getId(),
                round($order->getPaymentInstruction()->getAmount() - $order->getPaymentInstruction()->getDepositedAmount(), 2)
            );
        } else {
            $payment = $pendingTransaction->getPayment();
        }

        return $payment;
    }

    private function preValidate()
    {
        if (!$this->request->hasOrderId()) {
            $this->logger->info(
                'Request does not have orderId',
                array(
                    'requestId' => $this->request->getId(),
                )
            );
            throw new PaymentException('Malformed request');
        }
    }
}
