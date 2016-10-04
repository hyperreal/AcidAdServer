<?php

namespace Hyper\AdsBundle\Payment\Electrum\Processor;


use Doctrine\ORM\EntityManager;
use Hyper\AdsBundle\Exception\InvalidIpnRequestException;
use Hyper\AdsBundle\Exception\PaymentException;
use Hyper\AdsBundle\Payment\Electrum\OmnipayElectrumPaymentPlugin;
use Hyper\AdsBundle\Payment\Electrum\Requests\ElectrumIpnRequest;
use Hyper\AdsBundle\Payment\OrderInterface;
use Hyper\AdsBundle\Payment\Util\OrderApprovalDeterminerInterface;
use Hyper\AdsBundle\Payment\Util\OrderHashGeneratorInterface;
use JMS\Payment\CoreBundle\PluginController\PluginController;
use JMS\Payment\CoreBundle\PluginController\PluginControllerInterface;
use JMS\Payment\CoreBundle\PluginController\Result;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class ElectrumProcessor {

  /**
   * @var ContainerInterface
   */
  private $container;
  /**
   * @var OrderHashGeneratorInterface
   */
  private $orderHashGenerator;
  /**
   * @var EntityManager
   */
  private $em;
  /**
   * @var LoggerInterface
   */
  private $logger;
  /**
   * @var OrderApprovalDeterminerInterface
   */
  private $approvalDeterminer;
  /**
   * @var PluginControllerInterface
   */
  private $pluginController;
  /**
   * @var OmnipayElectrumPaymentPlugin
   */
  private $paymentPlugin;
  /**
   * @var ElectrumIpnRequest
   */
  private $request;

  public function __construct(
    OrderHashGeneratorInterface $orderHashGenerator,
    EntityManager $em,
    LoggerInterface $logger,
    OrderApprovalDeterminerInterface $approvalDeterminer,
    PluginController $pluginController,
    OmnipayElectrumPaymentPlugin $paymentPlugin
  ) {
    $this->orderHashGenerator = $orderHashGenerator;
    $this->em = $em;
    $this->logger = $logger;
    $this->approvalDeterminer = $approvalDeterminer;
    $this->pluginController = $pluginController;
    $this->pluginController->addPlugin($paymentPlugin);
    $this->request = $request;
  }

  public function process($id, $hash) {
    $this->request = new ElectrumIpnRequest($id, $hash);
    $order = $this->getOrder($id);
    $this->logger->info("IPN Electrum request: ", array(var_export($this->request, true)));

    $this->checkHash($order);

    if ($this->approvalDeterminer->shouldApprove($this->request)) {
      $this->savePaymentData($order);
      $this->acceptOrder($order);
    } else {
      $this->cancelOrder($order);
    }
  }

  private function checkHash(OrderInterface $order)
  {
    if ($this->request->getHash() != ($hashOrder = $this->orderHashGenerator->hashOrder($order))) {
      $this->logger->warning(
        'Electrum ipn request is invalid due to invalid hash ',
        array(
          'orderNumber' => $this->request->getId(),
          'requestHash' => $this->request->getHash(),
          'orderHash' => $hashOrder,
        )
      );
      throw new InvalidIpnRequestException("Electrum IPN request is invalid due to invalid hash.");
    }
  }

  private function getOrder($orderNumber) {
    /** @var $orderRepository \Hyper\AdsBundle\Entity\OrderRepository */
    $orderRepository = $this->em->getRepository('HyperAdsBundle:Order');

    $order = $orderRepository->findOneByOrderNumber($orderNumber);
    if (empty($order)) {
      $this->logger->error("Order of number: $orderNumber was not found!");
      throw new PaymentException("Order not found");
    }
    return $order;
  }

  private function savePaymentData(OrderInterface $order)
  {
    $payment = $this->getPaymentFromOrder($order);
    // @todo w bitpay cena była w requeście IPN, do zbadania. Electrum nie wysyła IPN jeśli nie uzbierano całej kwoty, więc powinno być ok
    $result = $this->pluginController->approveAndDeposit($payment->getId(), $payment->getTargetAmount());
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

  private function persistOrder(OrderInterface $order)
  {
    $this->em->persist($order);
    $this->em->flush($order);
  }

  /**
   * @param OrderInterface $order
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
        round($order->getPaymentInstruction()->getAmount() - $order->getPaymentInstruction()->getDepositedAmount(), 5)
      );
    } else {
      $payment = $pendingTransaction->getPayment();
    }

    return $payment;
  }

}
