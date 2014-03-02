<?php

namespace Hyper\AdsBundle;

use Hyper\AdsBundle\DependencyInjection\CompilerPass\PaymentGatewayCompilerPass;
use Hyper\AdsBundle\DependencyInjection\CompilerPass\PaymentParamsProviderCompilerPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class HyperAdsBundle extends Bundle
{
    public function boot()
    {
        $em       = $this->container->get('doctrine.orm.entity_manager');
        $platform = $em->getConnection()->getDatabasePlatform();
        $platform->registerDoctrineTypeMapping('enum', 'string');
    }

    public function build(ContainerBuilder $container)
    {
        parent::build($container);
        $container->addCompilerPass(new PaymentGatewayCompilerPass());
        $container->addCompilerPass(new PaymentParamsProviderCompilerPass());
    }


    public function getParent()
    {
        return 'FOSUserBundle';
    }
}
