<?php

namespace Hyper\AdsBundle\Payment\Processors;

use Doctrine\ORM\EntityManager;
use Hyper\AdsBundle\Exception\InvalidIpnRequestException;
use Hyper\AdsBundle\Exception\PaymentException;
use Hyper\AdsBundle\Payment\OmnipayBitPayPaymentPlugin;
use Hyper\AdsBundle\Payment\Requests\BitPayIpnRequest;
use Hyper\AdsBundle\Payment\Util\BitPayOrderApprovalDeterminer;
use JMS\Payment\CoreBundle\Plugin\Exception\Action\VisitUrl;
use JMS\Payment\CoreBundle\PluginController\PluginControllerInterface;
use JMS\Payment\CoreBundle\PluginController\Result;
use Omnipay\BitPay\Gateway;
use Psr\Log\LoggerInterface;
use Wikp\PaymentMtgoxBundle\Plugin\OrderInterface;

class BitPayIpnRequestProcessor
{
    /** @var \Hyper\AdsBundle\Payment\Requests\BitPayIpnRequest */
    private $request;

    /** @var \Omnipay\BitPay\Gateway */
    private $gateway;

    /** @var \Psr\Log\LoggerInterface */
    private $logger;

    /** @var \Doctrine\ORM\EntityManager */
    private $entityManager;

    /** @var \Hyper\AdsBundle\Payment\Util\BitPayOrderApprovalDeterminer */
    private $approvalDeterminer;

    /** @var \JMS\Payment\CoreBundle\PluginController\PluginControllerInterface */
    private $pluginController;

    private $bitPayApiKey;

    public function __construct(
        BitPayIpnRequest $request,
        Gateway $gateway,
        EntityManager $entityManager,
        OmnipayBitPayPaymentPlugin $paymentPlugin,
        PluginControllerInterface $paymentController,
        BitPayOrderApprovalDeterminer $approvalDeterminer,
        LoggerInterface $logger,
        $bitPayApiKey
    ) {
        $this->request = $request;
        $this->gateway = $gateway;
        $this->logger = $logger;
        $this->bitPayApiKey = $bitPayApiKey;
        $this->entityManager = $entityManager;
        $this->approvalDeterminer = $approvalDeterminer;
        $this->pluginController = $paymentController;
        $this->pluginController->addPlugin($paymentPlugin);
    }

    public function process()
    {
        $this->logger->info('Start processing of bitpay ipn request of id {i}', array('i' => $this->request->getId()));

        $this->checkHash();

        if ($this->request->isNew()) {
            $this->logger->notice(
                'Bitpay ipn request of id {id} is not yet confirmed (status: new)',
                array(
                    'id' => $this->request->getId(),
                    'status' => $this->request->getStatus(),
                )
            );
            return;
        }

        $order = $this->getOrder();
        if ($this->approvalDeterminer->shouldApprove($this->request)) {
            $this->savePaymentData($order);
            $this->acceptOrder($order);
        } elseif ($this->approvalDeterminer->shouldCancel($this->request)) {
            $this->cancelOrder($order);
        }
    }

    private function checkHash()
    {
        if ($this->request->getHash() != crypt($this->request->getOrderId(), $this->bitPayApiKey)) {
            $this->logger->warning(
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
            $this->logger->warning('Payment for order {orderId} is pending', array('orderId' => $order->getId()));
            throw $result->getPluginException();
        } elseif (Result::STATUS_SUCCESS !== $result->getStatus()) {
            throw new PaymentException();
        }
    }

    private function acceptOrder(OrderInterface $order)
    {
        $this->logger->info('Payment for order {orderId} was successful', array('orderId' => $order->getId()));
        $order->approve();
        $this->persistOrder($order);
    }

    private function cancelOrder(OrderInterface $order)
    {
        $this->logger->warning('Payment for order {orderId} was unsuccessful', array('orderId' => $order->getId()));
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
                'Pending transaction for order {orderId} not found',
                array('orderId' => $order->getId())
            );
            $payment = $this->pluginController->createPayment(
                $order->getPaymentInstruction()->getId(),
                $order->getPaymentInstruction()->getAmount() - $order->getPaymentInstruction()->getDepositedAmount()
            );
        } else {
            $payment = $$pendingTransaction->getPayment();
        }

        return $payment;
    }
} 