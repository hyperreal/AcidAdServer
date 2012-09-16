<?php

namespace Hyper\AdsBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

class HyperAdsBundle extends Bundle
{
    public function boot()
    {
        $em       = $this->container->get('doctrine.orm.entity_manager');
        $platform = $em->getConnection()->getDatabasePlatform();
        $platform->registerDoctrineTypeMapping('enum', 'string');
    }
}
