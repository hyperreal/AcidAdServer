<?php

namespace Hyper\AdsBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\DefinitionDecorator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;

/**
 * This is the class that loads and manages your bundle configuration
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class HyperAdsExtension extends Extension
{
    /**
     * {@inheritDoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config        = $this->processConfiguration($configuration, $configs);

        $loader = new Loader\XmlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));

        foreach ($config['payment_gateways'] as $gateway => $values) {
            $definitionId = sprintf('hyper_ads.payment_gateway.' . $gateway);
            $container->setDefinition(
                $definitionId,
                new DefinitionDecorator('hyper_ads.payment_gateways')
            )->setArguments(array($values));
        }

        $loader->load('services.xml');
    }
}
