<?php

namespace Hyper\AdsBundle\DependencyInjection\CompilerPass;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class PaymentParamsProviderCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition('hyper_ads.payment.params_providers')) {
            return;
        }

        $definition = $container->getDefinition('hyper_ads.payment.params_providers');
        $taggedServices = $container->findTaggedServiceIds('hyper_ads.payment.params_provider');

        foreach ($taggedServices as $id => $tagAttributes) {
            foreach ($tagAttributes as $attributes) {
                $definition->addMethodCall(
                    'addProvider',
                    array(new Reference($id), $attributes['system_name'])
                );
            }
        }
    }
}