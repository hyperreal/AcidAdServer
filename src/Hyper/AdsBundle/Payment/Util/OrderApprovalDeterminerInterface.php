<?php

namespace Hyper\AdsBundle\Payment\Util;

use Hyper\AdsBundle\Payment\Requests\AbstractOmnipayRequest;

interface OrderApprovalDeterminerInterface
{
    public function shouldApprove(AbstractOmnipayRequest $request);
    public function shouldCancel(AbstractOmnipayRequest $request);
} 