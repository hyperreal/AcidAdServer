<?php

namespace Hyper\AdsBundle\Payment\Electrum\Parameters;

use Hyper\AdsBundle\Payment\OrderInterface;
use Hyper\AdsBundle\Payment\ParamsProviderInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Translation\TranslatorInterface;

class ElectrumParamsProvider implements ParamsProviderInterface {


  /**
   * @var TranslatorInterface
   */
  private $translator;
  private $electrumExpiration;
  private $electrumEndpoint;

  public function __construct($electrumExpiration,
                              $electrumEndpoint,
                              TranslatorInterface $translator
  ) {
    $this->electrumExpiration = $electrumExpiration;
    $this->electrumEndpoint = $electrumEndpoint;
    $this->translator = $translator;
  }

  /**
   * @param OrderInterface $order
   * @return array
   */
  public function getParametersFromOrder(OrderInterface $order) {
    return array(
      'memo' => $this->translator->trans(
        'payment.info', 
        array('%orderNumber' => $order->getPaymentInstruction()->getId()),
        'HyperAdsBundle'
      ),
      'expirationTime' => $this->electrumExpiration,
      'amount' => $order->getPaymentInstruction()->getAmount(),
      'endpoint' => $this->electrumEndpoint,
    );
  }
}
