<?php

namespace Hyper\AdsBundle\DependencyInjection\CompilerPass;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class PaymentGatewayCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition('hyper_ads.payment_gateway_factory')) {
            return;
        }

        $definition = $container->getDefinition('hyper_ads.payment_gateway_factory');
        $taggedServices = $container->findTaggedServiceIds('hyper_ads.payment_gateway_builder');

        foreach ($taggedServices as $id => $tagAttributes) {
            foreach ($tagAttributes as $attributes) {
                $definition->addMethodCall(
                    'addGatewayBuilder',
                    array(new Reference($id), $attributes['gateway'])
                );
            }
        }
    }
}