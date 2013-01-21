<?php

namespace Wikp\PaymentMtgoxBundle\Controller;

use JMS\DiExtraBundle\Annotation as DI;
use JMS\Payment\CoreBundle\PluginController\Result;
use JMS\Payment\CoreBundle\Plugin\Exception\ActionRequiredException;
use JMS\Payment\CoreBundle\Plugin\Exception\Action\VisitUrl;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Wikp\PaymentMtgoxBundle\Exception\RuntimeException;
use Wikp\PaymentMtgoxBundle\Form\MtgoxIpnType;
use Wikp\PaymentMtgoxBundle\Form\IpnRequest;
use Wikp\PaymentMtgoxBundle\Form\Validator\IsValidIpnSignValidator;
use Wikp\PaymentMtgoxBundle\Form\Validator\IsValidIpnSign;
use Wikp\PaymentMtgoxBundle\Plugin\OrderInterface;

class IpnController extends Controller
{
    const RESPONSE_OK = '[OK]';

    /**
     * @DI\Inject("payment.plugin_controller")
     * @var \JMS\Payment\CoreBundle\PluginController\PluginControllerInterface
     */
    private $ppc;

    public function indexAction(Request $request)
    {
        $wholeRequest = $this->prepareRequestArray($request);

        $form = $this->createForm('wikp_mtgox_ipn');
        $form->bind($wholeRequest);

        if (!$form->isValid()) {
            throw new \InvalidArgumentException($form->getErrorsAsString());
            //throw new AccessDeniedException($form->getErrorsAsString());
        }

        if ($form->get('status') === MtgoxIpnType::STATUS_PARTIAL) {
            return new Response('partial payments not supported');
        }

        $this->ppc->addPlugin($this->get('wikp_payment_mtgox.plugin'));
        $order = $this->getOrderFromRepository($form->get('data')->getData());
        $paymentInstruction = $order->getPaymentInstruction();

        if (MtgoxIpnType::STATUS_CANCELLED === $form->get('status')) {

            $this->get('logger')->warn(
                sprintf(
                    'Payment IPN for order of ID=%d cancelled, paymentInstructionId=%d amount=%s',
                    $order->getId(),
                    $paymentInstruction->getId(),
                    $paymentInstruction->getAmount()
                )
            );

            return $this->cancelOrder($order);
        }

        if (null === ($pendingTransaction = $paymentInstruction->getPendingTransaction())) {
            $payment = $this->ppc->createPayment(
                $paymentInstruction->getId(),
                $paymentInstruction->getAmount() - $paymentInstruction->getDepositedAmount()
            );
        } else {
            $payment = $pendingTransaction->getPayment();
        }

        $result = $this->ppc->approveAndDeposit($payment->getId(), $payment->getTargetAmount());
        if (Result::STATUS_PENDING === $result->getStatus()) {
            $ex = $result->getPluginException();
            if ($ex instanceof ActionRequiredException) {
                $action = $ex->getAction();

                if ($action instanceof VisitUrl) {
                    return $this->redirect($action->getUrl());
                }

                $this->get('logger')->error(
                    sprintf(
                        'Payment IPN for order of ID=%d unsuccessful, paymentInstructionId=%d amount=%s targetAmount=%s, exceptionMessage=%s',
                        $order->getId(),
                        $paymentInstruction->getId(),
                        $paymentInstruction->getAmount(),
                        $payment->getTargetAmount(),
                        $ex->getMessage()
                    )
                );

                throw $ex;
            }
        } elseif (Result::STATUS_SUCCESS !== $result->getStatus()) {
            $this->get('logger')->error(
                sprintf(
                    'Payment IPN for order of ID=%d unsuccessful, paymentInstructionId=%d amount=%s targetAmount=%s, reasonCode=%s',
                    $order->getId(),
                    $paymentInstruction->getId(),
                    $paymentInstruction->getAmount(),
                    $payment->getTargetAmount(),
                    $result->getReasonCode()
                )
            );
            throw new RuntimeException('Transaction is unsuccessful: ' . $result->getReasonCode());
        }

        $this->get('logger')->warn(
            sprintf(
                'Payment IPN for order of ID=%d successful, paymentInstructionId=%d amount=%s targetAmount=%s',
                $order->getId(),
                $paymentInstruction->getId(),
                $paymentInstruction->getAmount(),
                $payment->getTargetAmount()
            )
        );

        /** @var $order \Hyper\AdsBundle\Entity\Order */
        $order->approve();
        $this->saveOrder($order);

        return new Response(self::RESPONSE_OK);
    }

    private function cancelOrder(OrderInterface $order)
    {
        $order->cancel();
        $this->saveOrder($order);
        return new Response(self::RESPONSE_OK);
    }

    private function saveOrder(OrderInterface $order)
    {
        $this->get('doctrine.orm.entity_manager')->persist($order);
        $this->get('doctrine.orm.entity_manager')->flush();
    }

    /**
     * @param $orderId
     *
     * @return \Wikp\PaymentMtgoxBundle\Plugin\OrderInterface
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    private function getOrderFromRepository($orderId)
    {
        /** @var $orderRepository \Wikp\PaymentMtgoxBundle\Plugin\OrderRepositoryInterface */
        $orderRepository = $this->get('wikp_payment_mtgox.order_repository')->getRepository();

        /** @var $order \Wikp\PaymentMtgoxBundle\Plugin\OrderInterface */
        $order = $orderRepository->find($orderId);

        if (empty($order) && !($order instanceof OrderInterface)) {
            throw $this->createNotFoundException('Order for given financial transaction ID does not exists');
        }

        return $order;
    }

    private function prepareRequestArray(Request $request)
    {
        if (!$request->server->has('HTTP_REST_SIGN')) {
            throw new AccessDeniedException("You didn't provide Rest-Sign header");
        }

        $wholeRequest = $request->request->all();
        unset($wholeRequest['ipnRequestObject']);

        $wholeRequest['ipnRequestObject'] = new IpnRequest(
            file_get_contents('php://input'),
            $request->server->get('HTTP_REST_SIGN')
        );

        return $wholeRequest;
    }
}
