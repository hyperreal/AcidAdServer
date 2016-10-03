<?php

namespace Hyper\AdsBundle\Payment\Electrum;

use Hyper\AdsBundle\Payment\Requests\AbstractOmnipayRequest;
use Hyper\AdsBundle\Payment\Util\OrderApprovalDeterminerInterface;

class ElectrumOrderApprovalDeterminer implements OrderApprovalDeterminerInterface
{
  public function shouldApprove(AbstractOmnipayRequest $request) {
    return true;
  }

  public function shouldCancel(AbstractOmnipayRequest $request) {
    return false;
  }
}