<?php

namespace Wikp\PaymentMtgoxBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html#cookbook-bundles-extension-config-class}
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritDoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();

        // Here you should define the parameters that are allowed to
        // configure your bundle. See the documentation linked above for
        // more information on that topic.

        return $treeBuilder
            ->root('wikp_payment_mtgox', 'array')
                ->children()
                    ->scalarNode('api_key')->isRequired()->end()
                    ->scalarNode('api_secret')->isRequired()->end()
                    ->scalarNode('order_repository_path')->isRequired()->end()
                    ->scalarNode('return_url')->isRequired()->end()
                    ->scalarNode('cancel_url')->isRequired()->end()
                    ->booleanNode('debug')->defaultValue('%kernel.debug%')->end()
                ->end()
            ->end();
    }
}
