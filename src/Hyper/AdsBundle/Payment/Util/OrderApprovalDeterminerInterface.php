<?php

namespace Hyper\AdsBundle\Payment\Util;

use Hyper\AdsBundle\Payment\Requests\AbstractOmnipayRequest;

interface OrderApprovalDeterminerInterface
{
    function shouldApprove(AbstractOmnipayRequest $request);
    function shouldCancel(AbstractOmnipayRequest $request);
} 