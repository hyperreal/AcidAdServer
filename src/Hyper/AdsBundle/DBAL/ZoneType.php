<?php
/**
 * @author fajka <fajka@hyperreal.info>
 * @see    https://github.com/fajka
 */

namespace Hyper\AdsBundle\DBAL;

use Doctrine\DBAL\Types\Type;
use Doctrine\DBAL\Platforms\AbstractPlatform;

class ZoneType extends Type
{
    const ZONE_TYPE = 'zonetype';

    const ZONE_TYPE_MOBILE  = 'mobile';
    const ZONE_TYPE_DESKTOP = 'desktop';

    public function getSQLDeclaration(array $fieldDeclaration, AbstractPlatform $platform)
    {
        return "ENUM('mobile', 'desktop') COMMENT '(DC2Type:zonetype)'";
    }

    public function convertToPHPValue($value, AbstractPlatform $platform)
    {
        return $value;
    }

    public function convertToDatabaseValue($value, AbstractPlatform $platform)
    {
        if (!in_array($value, array(self::ZONE_TYPE_DESKTOP, self::ZONE_TYPE_MOBILE))) {
            throw new \InvalidArgumentException('Invalid zone type');
        }

        return $value;
    }

    public function getName()
    {
        return self::ZONE_TYPE;
    }

    public static function getValidTypes()
    {
        return array(
            self::ZONE_TYPE_DESKTOP,
            self::ZONE_TYPE_MOBILE,
        );
    }

    public static function getValidTypesWithLabels()
    {
        return array(
            self::ZONE_TYPE_DESKTOP => 'desktop',
            self::ZONE_TYPE_MOBILE => 'mobile',
        );
    }
}
