<?php

namespace Hyper\AdsBundle\Payment\Util;

use Hyper\AdsBundle\Payment\BitPayStatus;
use Hyper\AdsBundle\Payment\Requests\AbstractOmnipayRequest;

class BitPayOrderApprovalDeterminer implements OrderApprovalDeterminerInterface
{
    function shouldApprove(AbstractOmnipayRequest $request)
    {
        return in_array($request->getStatus(), BitPayStatus::getCompletedStatuses());
    }

    function shouldCancel(AbstractOmnipayRequest $request)
    {
        return in_array($request->getStatus(), BitPayStatus::getNonCompletedStatuses());
    }
}