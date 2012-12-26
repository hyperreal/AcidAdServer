<?php

namespace Hyper\AdsBundle\DBAL;

use Doctrine\DBAL\Types\Type;
use Doctrine\DBAL\Platforms\AbstractPlatform;

class AnnouncementPaymentType extends Type
{
    const ANNOUNCEMENT_PAYMENT_TYPE = 'announcement_payment_type';

    const ANNOUNCEMENT_PAYMENT_TYPE_STANDARD  = 'standard';
    const ANNOUNCEMENT_PAYMENT_TYPE_PREMIUM = 'premium';

    public function getSQLDeclaration(array $fieldDeclaration, AbstractPlatform $platform)
    {
        return sprintf(
            "ENUM('%s', '%s') COMMENT '(DC2Type:announcement_payment_type)'",
            self::ANNOUNCEMENT_PAYMENT_TYPE_STANDARD,
            self::ANNOUNCEMENT_PAYMENT_TYPE_PREMIUM
        );
    }

    public function convertToPHPValue($value, AbstractPlatform $platform)
    {
        return $value;
    }

    public function convertToDatabaseValue($value, AbstractPlatform $platform)
    {
        if (!in_array($value, self::getValidTypes())) {
            throw new \InvalidArgumentException('Invalid announcement payment type');
        }

        return $value;
    }

    public function getName()
    {
        return self::ANNOUNCEMENT_PAYMENT_TYPE;
    }

    public static function getValidTypes()
    {
        return array(
            self::ANNOUNCEMENT_PAYMENT_TYPE_PREMIUM,
            self::ANNOUNCEMENT_PAYMENT_TYPE_STANDARD,
        );
    }

    public static function getValidTypesWithLabels()
    {
        return array(
            self::ANNOUNCEMENT_PAYMENT_TYPE_PREMIUM => 'announcement.premium',
            self::ANNOUNCEMENT_PAYMENT_TYPE_STANDARD => 'announcement.standard',
        );
    }
}
