<?php
/**
 * @author fajka <fajka@hyperreal.info>
 * @see    https://github.com/fajka
 */

namespace Hyper\AdsBundle\DBAL;

use Doctrine\DBAL\Types\Type;
use Doctrine\DBAL\Platforms\AbstractPlatform;

class PayModelType extends Type
{
    const BANNER_TYPE = 'paymodeltype';

    const PAY_MODEL_DAILY = 'daily';
    const PAY_MODEL_PPV = 'ppv';
    const PAY_MODEL_PPC = 'ppc';

    public function getSQLDeclaration(array $fieldDeclaration, AbstractPlatform $platform)
    {
        return "ENUM('daily', 'ppc', 'ppv') COMMENT '(DC2Type:paymodeltype)'";
    }

    public function convertToPHPValue($value, AbstractPlatform $platform)
    {
        return $value;
    }

    public function convertToDatabaseValue($value, AbstractPlatform $platform)
    {
        if (!in_array($value, self::getValidTypes())) {
            throw new \InvalidArgumentException('Invalid pay model type');
        }

        return $value;
    }

    public function getName()
    {
        return self::BANNER_TYPE;
    }

    public static function getValidTypes()
    {
        return array(
            self::PAY_MODEL_DAILY,
            self::PAY_MODEL_PPC,
            self::PAY_MODEL_PPV,
        );
    }

    public static function getValidTypesWithLabels()
    {
        return array(
            self::PAY_MODEL_DAILY => 'pay.for.day.of.emission',
            self::PAY_MODEL_PPC => 'pay.per.click',
            self::PAY_MODEL_PPV => 'pay.per.view',
        );
    }
}
