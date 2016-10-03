<?php

namespace Hyper\AdsBundle\Payment\BitPay;

use Hyper\AdsBundle\Payment\BitPay\Parameters\BitPayStatus;
use Hyper\AdsBundle\Payment\Requests\AbstractOmnipayRequest;
use Hyper\AdsBundle\Payment\Util\OrderApprovalDeterminerInterface;

class BitPayOrderApprovalDeterminer implements OrderApprovalDeterminerInterface
{
    public function shouldApprove(AbstractOmnipayRequest $request)
    {
        return in_array($request->getStatus(), BitPayStatus::getCompletedStatuses());
    }

    public function shouldCancel(AbstractOmnipayRequest $request)
    {
        return in_array($request->getStatus(), BitPayStatus::getNonCompletedStatuses());
    }
}