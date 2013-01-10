<?php

namespace Wikp\PaymentMtgoxBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;

/**
 * This is the class that loads and manages your bundle configuration
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class WikpPaymentMtgoxExtension extends Extension
{
    /**
     * {@inheritDoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yml');

        $container->setParameter('wikp_payment_mtgox.api_key', $config['api_key']);
        $container->setParameter('wikp_payment_mtgox.api_secret', $config['api_secret']);
        $container->setParameter('wikp_payment_mtgox.order_repository_path', $config['order_repository_path']);

        $container->setParameter('wikp_payment_mtgox.return_url', $config['return_url']);
        $container->setParameter('wikp_payment_mtgox.cancel_url', $config['cancel_url']);
        $container->setParameter('wikp_payment_mtgox.debug', $config['debug']);
    }
}
