<?php
/**
 * @author fajka <fajka@hyperreal.info>
 * @see    https://github.com/fajka
 */

namespace Hyper\AdsBundle\DBAL;

use Doctrine\DBAL\Types\Type;
use Doctrine\DBAL\Platforms\AbstractPlatform;

class BannerType extends Type
{

    const BANNER_TYPE = 'bannertype';

    const BANNER_TYPE_TEXT  = 'text';
    const BANNER_TYPE_IMAGE = 'image';
    const BANNER_TYPE_FLASH = 'flash';

    public function getSQLDeclaration(array $fieldDeclaration, AbstractPlatform $platform)
    {
        return "ENUM('text', 'image', 'flash') COMMENT '(DC2Type:bannertype)'";
    }

    public function convertToPHPValue($value, AbstractPlatform $platform)
    {
        return $value;
    }

    public function convertToDatabaseValue($value, AbstractPlatform $platform)
    {
        if (!in_array($value, array(self::BANNER_TYPE_FLASH, self::BANNER_TYPE_IMAGE, self::BANNER_TYPE_TEXT))) {
            throw new \InvalidArgumentException('Invalid banner type');
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
            self::BANNER_TYPE_FLASH,
            self::BANNER_TYPE_IMAGE,
            self::BANNER_TYPE_TEXT,
        );
    }
}
