<?php

namespace Hyper\AdsBundle\Payment;

final class BitPayStatus
{
    const STATUS_NEW = 'new';
    const STATUS_PAID = 'paid';
    const STATUS_CONFIRMED = 'confirmed';
    const STATUS_COMPLETE = 'complete';
    const STATUS_INVALID = 'invalid';
    const STATUS_EXPIRED = 'expired';

    public static function getCompletedStatuses()
    {
        return array(
            BitPayStatus::STATUS_PAID,
            BitPayStatus::STATUS_CONFIRMED,
            BitPayStatus::STATUS_COMPLETE,
        );
    }

    public static function getNonCompletedStatuses()
    {
        return array(
            BitPayStatus::STATUS_INVALID,
            BitPayStatus::STATUS_EXPIRED,
        );
    }
}