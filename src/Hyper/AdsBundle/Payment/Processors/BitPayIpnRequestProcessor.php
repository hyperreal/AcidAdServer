<?php

namespace Hyper\AdsBundle\Payment\Processors;

use Doctrine\ORM\EntityManager;
use Hyper\AdsBundle\Exception\InvalidIpnRequestException;
use Hyper\AdsBundle\Exception\PaymentException;
use Hyper\AdsBundle\Payment\OmnipayBitPayPaymentPlugin;
use Hyper\AdsBundle\Payment\OrderInterface;
use Hyper\AdsBundle\Payment\Requests\BitPayIpnRequest;
use Hyper\AdsBundle\Payment\Util\BitPayOrderApprovalDeterminer;
use Hyper\AdsBundle\Payment\Util\OrderHashGeneratorInterface;
use JMS\Payment\CoreBundle\PluginController\PluginControllerInterface;
use JMS\Payment\CoreBundle\PluginController\Result;
use Psr\Log\LoggerInterface;

class BitPayIpnRequestProcessor
{
    /** @var \Hyper\AdsBundle\Payment\Requests\BitPayIpnRequest */
    private $request;

    /** @var \Psr\Log\LoggerInterface */
    private $logger;

    /** @var \Doctrine\ORM\EntityManager */
    private $entityManager;

    /** @var \Hyper\AdsBundle\Payment\Util\BitPayOrderApprovalDeterminer */
    private $approvalDeterminer;

    /** @var \JMS\Payment\CoreBundle\PluginController\PluginControllerInterface */
    private $pluginController;

    /** @var \Hyper\AdsBundle\Payment\Util\OrderHashGeneratorInterface */
    private $hashOrderGenerator;

    public function __construct(
        BitPayIpnRequest $request,
        EntityManager $entityManager,
        OmnipayBitPayPaymentPlugin $paymentPlugin,
        PluginControllerInterface $paymentController,
        BitPayOrderApprovalDeterminer $approvalDeterminer,
        LoggerInterface $logger,
        OrderHashGeneratorInterface $hashOrderGenerator
    ) {
        $this->request = $request;
        $this->logger = $logger;
        $this->entityManager = $entityManager;
        $this->approvalDeterminer = $approvalDeterminer;
        $this->pluginController = $paymentController;
        $this->pluginController->addPlugin($paymentPlugin);
        $this->hashOrderGenerator = $hashOrderGenerator;
    }

    public function process()
    {
        $this->requestDebugInfo();
        $this->preValidate();
        $order = $this->getOrder();
        $this->checkHash($order);

        if ($this->request->isNew()) {
            $this->logger->info(
                'Bitpay ipn request of id {id} is not yet confirmed (status: new)',
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
        if ($this->request->getHash() != $this->hashOrderGenerator->hashOrder($order)) {
            $this->logger->info(
                'Bitpay ipn request of id {id} (order: {orderId}) is invalid due to invalid hash',
                array('id' => $this->request->getId(), 'orderId' => $this->request->getOrderId())
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
                'Payment for order {orderId} is pending (request id: {requestId})',
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
            'Payment for order {orderId} was successful (request id: {requestId})',
            array('orderId' => $order->getId(), 'requestId' => $this->request->getId())
        );
        $order->approve();
        $this->persistOrder($order);
    }

    private function cancelOrder(OrderInterface $order)
    {
        $this->logger->info(
            'Payment for order {orderId} was unsuccessful (request id: {requestId})',
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
                'Pending transaction for order {orderId} not found (request id: {requestId})',
                array(
                    'orderId' => $order->getId(),
                    'requestId' => $this->request->getId(),
                )
            );
            $payment = $this->pluginController->createPayment(
                $order->getPaymentInstruction()->getId(),
                $order->getPaymentInstruction()->getAmount() - $order->getPaymentInstruction()->getDepositedAmount()
            );
        } else {
            $payment = $pendingTransaction->getPayment();
        }

        return $payment;
    }

    private function requestDebugInfo()
    {
        $this->logger->debug("Full request (id: {requestId}):\n\nSERVER:\n{server}\n\nPOST:\n{post}\n\nCOOKIE:\n{cookie}",
            array(
                'server' => var_export($_SERVER, true),
                'post' => var_export($_POST, true),
                'cookie' => var_export($_COOKIE, true),
                'requestId' => $this->request->getId(),
            )
        );
    }

    private function preValidate()
    {
        if (!$this->request->hasOrderId()) {
            $this->logger->info(
                'Request of ID: {requestId} does not have orderId',
                array(
                    'requestId' => $this->request->getId(),
                )
            );
            throw new PaymentException('Malformed request');
        }
    }
} 